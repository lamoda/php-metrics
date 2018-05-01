# Collecting metrics

Collector is a service with a single purpose - form and return *MetricSource*
In some cases collector can hold source directly, in other cases 
it can combine, merge, adopt other source in order to create final source. 
Also collector can create source by itself.

So general collector sample would be:

```php
<?php

$metric = new \Lamoda\Metric\Common\Metric('sample', 1.0, ['tag' => 'value']);
$source = new \Lamoda\Metric\Common\Source\IterableMetricSource([$metric]);
$collector = new \Lamoda\Metric\Collector\SingleSourceCollector($source);

$collectedSource = $collector->collect();
```

This collector is simple and just holds the preconfigured source to collect it on demand.
More complex example is `MergingCollector` which will combine metrics from different sources into one.

Utility example is `TaggingCollectorDecorator` which will add default tag values 
for each metric collected by delegate.

Collector is a kind of source wrapper in order to stabilize source definition and configuration for further purposes.

You can also use a *Storage* as hold source

## Helpers

`CollectorRegistry` can be used to hold named collectors
