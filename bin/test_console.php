<?php

namespace Lamoda\Metric\MetricBundle\Tests;

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;

$application = new Application(
    new Fixtures\TestKernel('test', true)
);

$input = new ArgvInput();
$application->run($input);
