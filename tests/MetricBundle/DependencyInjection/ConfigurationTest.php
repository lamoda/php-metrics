<?php

namespace Lamoda\Metric\MetricBundle\Tests\DependencyInjection;

use Lamoda\Metric\MetricBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * @covers \Lamoda\Metric\MetricBundle\DependencyInjection\Configuration
 */
final class ConfigurationTest extends TestCase
{
    public function getSamples(): array
    {
        $files = glob(__DIR__ . '/valid_config_samples/*.yml');

        $samples = array_combine($files, $files);
        $samples = array_map(
            function (string $fname) {
                return [$fname];
            },
            $samples
        );

        return $samples;
    }

    /**
     * @dataProvider getSamples
     *
     * @param string $fname
     */
    public function testValidConfigurations($fname)
    {
        $configuration = new Configuration();

        $tree = $configuration->getConfigTreeBuilder()->buildTree();

        $data = Yaml::parse(file_get_contents($fname));

        $config = $tree->finalize($data);

        self::assertArrayHasKey('sources', $config);
        self::assertArrayHasKey('receivers', $config);
        self::assertArrayHasKey('collectors', $config);
        self::assertArrayHasKey('responders', $config);
    }
}
