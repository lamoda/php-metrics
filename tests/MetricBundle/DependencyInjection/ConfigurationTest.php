<?php

namespace Lamoda\MetricBundle\Tests\DependencyInjection;

use Lamoda\MetricBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class ConfigurationTest extends TestCase
{
    public function getSamples(): array
    {
        $files = glob(__DIR__ . '/valid_config_samples/*.yml');

        $samples = array_combine($files, $files);
        $samples = array_map(
            function ($fname) {
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

        self::assertArrayHasKey('metrics', $config);
        self::assertArrayHasKey('groups', $config);
        self::assertArrayHasKey('sources', $config['metrics']);
        self::assertArrayHasKey('sources', $config['groups']);
        self::assertArrayHasKey('custom', $config['groups']);
    }
}
