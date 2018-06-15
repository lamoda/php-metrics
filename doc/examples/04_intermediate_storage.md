# Using intermediate storage for responding

Here is more complex example. You can use intermediate storage as
the source to collect the metrics from. General steps are:

1. Configure initial sources
2. Create collector on top of them
3. Materialize collector to the storage
4. Create new collector with storage as source of metrics
5. Create responder using this collector

Repeat steps 1-3 in order to update metrics being responded. 

```php
<?php

use Lamoda\Metric\Collector\SingleSourceCollector;
use Lamoda\Metric\Common\Source\IterableMetricSource;
use Lamoda\Metric\Common\Metric;
use Lamoda\Metric\Common\MetricInterface;
use Lamoda\Metric\Responder\ResponseFactory\TelegrafJsonResponseFactory;
use Lamoda\Metric\Storage\MetricStorageInterface;
use Lamoda\Metric\Responder\PsrResponder;

/** @var MetricInterface $metric */
$metric = new Metric('sample', time(), ['tag' => 'value']);
// 1. Configure initial storage
$source = new IterableMetricSource([$metric]);
// 2. Create collector on top of it
$collector = new SingleSourceCollector($source);
// See storage examples
/** @var MetricStorageInterface$storage */
$storage = new ArrayStorage();
// 3. Materialize collector to the storage
$storage->receive($collector->collect());
// At this moment you can persist or cache your collected metrics
// if the storage driver supports restoring process

// 4. Create new collector with storage as source of metrics
$responderCollector = new SingleSourceCollector($storage);

// 5. Create responder using this collector
$formatter = new TelegrafJsonResponseFactory();
$responder = new PsrResponder($responderCollector, $formatter, ['prefix' => 'my_metric_']);
$response = $responder->createResponse();

