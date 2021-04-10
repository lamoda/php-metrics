<?php

namespace Lamoda\Metric\MetricBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\SchemaValidator;
use Lamoda\Metric\MetricBundle\Tests\Fixtures\TestKernel;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractMetricBundleTest extends WebTestCase
{
    /** @var Client */
    protected static $client;
    /** @var EntityManagerInterface */
    protected static $em;

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    protected static function getEntityManager(): EntityManagerInterface
    {
        if (static::$em === null) {
            /** @var EntityManagerInterface $em */
            static::$em = static::$kernel->getContainer()->get('doctrine')->getManager();
        }

        return static::$em;
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

    protected function tearDown(): void
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
    protected function setUp(): void
    {
        static::$client = static::createClient();
        self::mockDoctrine();
    }
}
