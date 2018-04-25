## Standalone responder usage

* You can create own metric and source it to formatter as a single metric group. 
* You can source multiple groups at once
* You can group multiple metrics in one groups

```php
<?php

use Lamoda\Metric\Common\MetricInterface;
use Lamoda\Metric\Common\Source\IterableMetricSource;
use Lamoda\Metric\Responder\ResponseFactory\TelegrafJsonResponseFactory;

final class MyMetric implements MetricInterface 
{
    public function getName(): string {
        return 'duck_count';
    }
    
    public function resolve(): float {
        // some heavy calculations here
        
        return 241;
    }
    
    public function getTags(): array {
        return ['type'=>'counter'];
    }
}

/** @var MetricInterface $metric */
$metric = new MyMetric();
// Source is iterable lazy metric source
$source = new IterableMetricSource([$metric]);
// Factory helps you represent your c
$factory = new TelegrafJsonResponseFactory();

// PSR-7 Response 
$response = $factory->create($$source, ['group_by_tag' => 'type', 'propagate_tags' => ['type']]);
```
