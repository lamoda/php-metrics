<?php

namespace Lamoda\MetricBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\SchemaValidator;
use Lamoda\MetricBundle\Tests\Fixtures\Entity\Metric;
use Lamoda\MetricBundle\Tests\Fixtures\Entity\MetricGroup;
use Lamoda\MetricBundle\Tests\Fixtures\TestKernel;
use Lamoda\MetricStorage\AdjustableMetricStorageInterface;
use Lamoda\MetricStorage\Exception\MetricStorageException;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class ResponderControllerTest extends WebTestCase
{
    /** @var Client */
    private static $client;
    /** @var EntityManagerInterface */
    private static $em;

    /**
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public static function setUpBeforeClass()
    {
        static::$client = static::createClient();
        self::mockDoctrine();
    }

    protected static function getKernelClass()
    {
        return TestKernel::class;
    }

    protected static function getContainer(): ContainerInterface
    {
        return static::$client->getContainer();
    }

    /**
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    private static function mockDoctrine()
    {
        $entityManager = static::getEntityManager();
        $tool = new SchemaTool($entityManager);
        $tool->dropDatabase();
        $tool->createSchema($entityManager->getMetadataFactory()->getAllMetadata());
        self::validateEntityManager();
    }

    private static function validateEntityManager()
    {
        $validator = new SchemaValidator(static::getEntityManager());
        $errors = $validator->validateMapping();
        static::assertCount(
            0,
            $errors,
            implode(
                "\n\n",
                array_map(
                    function ($l) {
                        return implode("\n\n", $l);
                    },
                    $errors
                )
            )
        );
    }

    private static function getEntityManager(): EntityManagerInterface
    {
        if (static::$em === null) {
            /** @var EntityManagerInterface $em */
            static::$em = static::$kernel->getContainer()->get('doctrine')->getManager();
        }

        return static::$em;
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function testMetricsReturned()
    {
        $container = static::getContainer();
        $doctrine = $this->persistMetrics($container);
        $this->assertAdjustableMetric($container, $doctrine);
        $this->assertPrometheus();
        $this->assertTelegraf('/metrics/telegraf');
        $this->assertTelegraf('/metrics/custom_telegraf');
    }

    private function assertTelegraf(string $url)
    {
        static::$client->request('GET', $url);
        $response = static::$client->getResponse();
        self::assertNotNull($response);
        self::assertTrue($response->isSuccessful());
        self::assertFalse($response->isCacheable());

        self::assertJsonStringEqualsJsonString(
            <<<'JSON'
[
  {
    "test_1": 246,
    "test_2": 12.3,
    "test_3": 17,
    "type": "doctrine_group"
  },
  {
    "test_1": 246,
    "test_2": 12.3,
    "test_3": 17,
    "test_4": 5.5,
    "custom_metric_for_composite": 2.2,
    "custom_metric": 1,
    "type": "custom"
  }
]
JSON
            ,
            $response->getContent()
        );
    }

    private function assertPrometheus()
    {
        static::$client->request('GET', '/metrics/prometheus');
        $response = static::$client->getResponse();
        self::assertNotNull($response);
        self::assertTrue($response->isSuccessful());
        self::assertFalse($response->isCacheable());

        self::assertSame(
            <<<'PROMETHEUS'
metrics_test_1{type="doctrine_group"} 246
metrics_test_2{type="doctrine_group"} 12.3
metrics_test_3{type="doctrine_group"} 17
metrics_test_1{type="custom"} 246
metrics_test_3{type="custom"} 17
metrics_test_4{type="custom"} 5.5
metrics_test_2{type="custom"} 12.3
metrics_custom_metric_for_composite{type="custom"} 2.2
metrics_custom_metric{type="custom"} 1

PROMETHEUS
            ,
            $response->getContent()
        );
    }

    /**
     * @param ContainerInterface     $container
     * @param EntityManagerInterface $doctrine
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function assertAdjustableMetric(ContainerInterface $container, EntityManagerInterface $doctrine)
    {
        /** @var AdjustableMetricStorageInterface $adjuster */
        $adjuster = $container->get(AdjustableMetricStorageInterface::class);

        self::assertTrue($adjuster->hasAdjustableMetric('test_1'));

        /** @var Metric $metric */
        $metric = $adjuster->getAdjustableMetric('test_1');

        self::assertNotNull($metric);
        $metric->adjust(5);

        $doctrine->clear();

        self::assertFalse($adjuster->hasAdjustableMetric('custom_metric'));
        try {
            $adjuster->getAdjustableMetric('custom_metric');
            self::fail('Should throw an exception');
        } catch (MetricStorageException $exception) {
        }
    }

    /**
     * @param $container
     *
     * @return EntityManagerInterface
     */
    private function persistMetrics($container): EntityManagerInterface
    {
        /** @var EntityManagerInterface $doctrine */
        $doctrine = $container->get('doctrine.orm.entity_manager');

        $m1 = new Metric('test_1', 241.0);
        $m2 = new Metric('test_2', 12.3);
        $m3 = new Metric('test_3', 17.0);
        $m4 = new Metric('test_4', 5.5);
        $doctrine->persist($m1);
        $doctrine->persist($m3);
        $doctrine->persist($m4);
        $doctrine->persist($m2);

        $group = new MetricGroup('doctrine_group', [$m1, $m2, $m4]);
        $group->addMetric($m3);
        $group->removeMetric($m4);
        $group->setTag('type', 'doctrine_group');
        $group->setTag('invalid', '');
        $group->removeTag('invalid');

        $doctrine->persist($group);

        self::assertSame('doctrine_group', $group->getName());
        self::assertSame([$m1, $m2, $m3], array_values(iterator_to_array($group)));

        $doctrine->flush();
        $doctrine->clear();

        return $doctrine;
    }
}
