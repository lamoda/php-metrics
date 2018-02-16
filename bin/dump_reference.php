<?php

/** @codeCoverageIgnore */
use Lamoda\MetricBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Dumper\YamlReferenceDumper;

require_once __DIR__ . '/../vendor/autoload.php';

$dumper = new YamlReferenceDumper();
echo $dumper->dump(new Configuration());
