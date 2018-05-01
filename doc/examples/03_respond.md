# Responding metrics

The general idea of responding is to combine metric collector 
and metric response formatter in order to create proper response
for metrics collected with collector

So the general example code is:

```php
<?php
// Create sample collector
/** @var MetricInterface $metric */
$metric = new Metric('sample', 241.0, ['tag' => 'value']);
// Source is iterable lazy metric source
$source = new IterableMetricSource([$metric]);
// You can use your own collector here
$collector = new SingleSourceCollector($source);
$formatter = new TelegrafJsonResponseFactory();

// We have PSR-7 Response here
$response = $formatter->create($collector->collect(), ['prefix' => 'my_metric_']);
