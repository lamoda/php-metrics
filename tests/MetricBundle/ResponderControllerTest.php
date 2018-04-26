<?php

namespace Lamoda\Metric\MetricBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\SchemaValidator;
use Lamoda\Metric\MetricBundle\Tests\Fixtures\Entity\Metric;
use Lamoda\Metric\MetricBundle\Tests\Fixtures\TestKernel;
use Lamoda\Metric\Storage\MetricMutatorInterface;
use Lamoda\Metric\Storage\MetricStorageInterface;
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
 * @covers \Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory\Storage
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
    public function testTelegrafMetricsReturned(string $path): void
    {
        $container = static::getContainer();
        $this->persistMetrics($container);
        static::$client->request('GET', $path);
        $response = static::$client->getResponse();
        self::assertNotNull($response);
        self::assertTrue($response->isSuccessful());
        self::assertFalse($response->isCacheable());

        self::assertJsonStringEqualsJsonString(
            <<<'JSON'
{
    "custom_metric": 1,
    "custom_metric_for_composite": 2.2
}
JSON
            ,
            $response->getContent()
        );
    }

    public function testPrometheusMetricsReturned(): void
    {
        $container = static::getContainer();
        $this->persistMetrics($container);
        static::$client->request('GET', '/metrics/prometheus');
        $response = static::$client->getResponse();
        self::assertNotNull($response);
        self::assertTrue($response->isSuccessful());
        self::assertFalse($response->isCacheable());

        self::assertSame(
            <<<'PROMETHEUS'
metrics_custom_metric{collector="raw"} 1
metrics_custom_metric_for_composite{collector="raw"} 2.2

PROMETHEUS
            ,
            $response->getContent()
        );
    }

    public function testAdjustableMetrics(): void
    {
        /** @var MetricMutatorInterface $adjuster */
        $adjuster = self::getContainer()->get('test.' . MetricMutatorInterface::class);
        /** @var MetricStorageInterface $storage */
        $storage = self::getContainer()->get('test.doctrine_metric_storage');

        $adjuster->adjustMetric(10, 'test_1', ['tag' => 'value1']);
        $adjuster->adjustMetric(20, 'test_1', ['tag' => 'value2']);

        $metric1 = $storage->findMetric('test_1', ['tag' => 'value1']);
        $metric2 = $storage->findMetric('test_1', ['tag' => 'value2']);
        self::assertNotNull($metric1);
        self::assertNotNull($metric2);

        self::assertNotSame($metric1, $metric2);
        self::assertEquals(10, $metric1->resolve());
        self::assertEquals(20, $metric2->resolve());

        $adjuster->adjustMetric(30, 'test_1', ['tag' => 'value1']);
        $metric3 = $storage->findMetric('test_1', ['tag' => 'value1']);
        self::assertNotNull($metric3);
        self::assertEquals($metric1, $metric3);
        self::assertEquals(40, $metric3->resolve());
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
