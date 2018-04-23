<?php

namespace Lamoda\Metric\MetricBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\SchemaValidator;
use Lamoda\Metric\MetricBundle\Tests\Fixtures\Entity\Metric;
use Lamoda\Metric\MetricBundle\Tests\Fixtures\TestKernel;
use Lamoda\Metric\Storage\AdjustableMetricStorageInterface;
use Lamoda\Metric\Storage\Exception\MetricStorageException;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @covers \Lamoda\Metric\MetricBundle\DependencyInjection\LamodaMetricExtension
 * @covers \Lamoda\Metric\MetricBundle\DependencyInjection\Configuration
 * @covers \Lamoda\Metric\MetricBundle\DependencyInjection\Compiler\RegisterCollectorsPass
 * @covers \Lamoda\Metric\MetricBundle\DependencyInjection\Compiler\RegisterReceiversPass
 * @covers \Lamoda\Metric\MetricBundle\DependencyInjection\Compiler\RegisterResponseFactoriesPass
 * @covers \Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory\Collector
 * @covers \Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory\Receiver
 * @covers \Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory\Responder
 * @covers \Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory\ResponseFactory
 * @covers \Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory\Source
 * @runTestsInSeparateProcesses
 */
final class ResponderControllerTest extends WebTestCase
{
    /** @var Client */
    private static $client;
    /** @var EntityManagerInterface */
    private static $em;

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    protected static function createKernel(array $options = [])
    {
        $kernel = parent::createKernel($options);
        $fs = new Filesystem();
        $fs->remove($kernel->getCacheDir());
        $fs->remove($kernel->getLogDir());

        return $kernel;
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

    public function getTelegrafTestRoutes(): array
    {
        return [
            'basic' => ['/metrics/telegraf_json'],
            'custom' => ['/metrics/custom_telegraf'],
        ];
    }

    /**
     * @param string $path
     *
     * @dataProvider getTelegrafTestRoutes
     */
    public function testTelegrafMetricsReturned(string $path)
    {
        $container = static::getContainer();
        $doctrine = $this->persistMetrics($container);
        $this->assertAdjustableMetric($container, $doctrine);
        $this->assertTelegraf($path);
    }

    public function testPrometheusMetricsReturned()
    {
        $container = static::getContainer();
        $doctrine = $this->persistMetrics($container);
        $this->assertAdjustableMetric($container, $doctrine);
        $this->assertPrometheus();
    }

    protected function tearDown()
    {
        $fs = new Filesystem();
        static::$em->close();
        $cacheDir = static::$kernel->getCacheDir();
        $logDir = static::$kernel->getLogDir();

        parent::tearDown();
        $fs->remove($cacheDir);
        $fs->remove($logDir);
    }

    /**
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    protected function setUp()
    {
        static::$client = static::createClient();
        self::mockDoctrine();
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
    "test_3": 17,
    "test_4": 5.5,
    "test_2": 12.3,
    "type": "doctrine_group"
  },
  {
    "custom_metric": 1,
    "custom_metric_for_composite": 2.2,
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
metrics_test_1{type="doctrine_group",own_tag="m1"} 246
metrics_test_3{type="doctrine_group",own_tag="m3"} 17
metrics_test_4{type="doctrine_group",own_tag="m4"} 5.5
metrics_test_2{type="doctrine_group",own_tag="m2"} 12.3
metrics_custom_metric{type="custom"} 1
metrics_custom_metric_for_composite{type="custom"} 2.2

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
        $adjuster = $container->get('test.' . AdjustableMetricStorageInterface::class);

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

        $m1 = new Metric('test_1', 241.0, ['own_tag' => 'm1']);
        $m2 = new Metric('test_2', 12.3, ['own_tag' => 'm2']);
        $m3 = new Metric('test_3', 17.0, ['own_tag' => 'm3']);
        $m4 = new Metric('test_4', 5.5, ['own_tag' => 'm4']);
        $doctrine->persist($m1);
        $doctrine->persist($m3);
        $doctrine->persist($m4);
        $doctrine->persist($m2);

        $doctrine->flush();
        $doctrine->clear();

        return $doctrine;
    }
}
