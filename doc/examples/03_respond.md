# Storageless responding

The general idea of responding is to combine metric collector 
and metric response formatter in order to create proper response
for metrics collected with collector

So the general example code is:

```php
<?php

use Lamoda\Metric\Collector\SingleSourceCollector;
use Lamoda\Metric\Common\Source\IterableMetricSource;
use Lamoda\Metric\Common\Metric;
use Lamoda\Metric\Common\MetricInterface;
use Lamoda\Metric\Responder\ResponseFactory\TelegrafJsonResponseFactory;
use Lamoda\Metric\Responder\PsrResponder;

/** @var MetricInterface $metric */
$metric = new Metric('sample', time(), ['tag' => 'value']);
// Source is iterable lazy metric source
$source = new IterableMetricSource([$metric]);
// You can use your own collector here
$collector = new SingleSourceCollector($source);
$formatter = new TelegrafJsonResponseFactory();

$responder = new PsrResponder($collector, $formatter, ['prefix' => 'my_metric_']);
// We have PSR-7 Response here
$response = $responder->createResponse();
```

This example illustrates the common approach to the metric responding which 
performs synchronous metric computation while formatting metric response
