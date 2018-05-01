# Storing and mutating metrics

Storage is responsible for several operations:

* Finding single metric by name and tags
* Operating as a metric source (find all metrics)
* Creating new metrics suitable for storage
* Receiving metrics (in a for of source) to be stored in

Also you can configure a `MetricMutator` with a single storage
and use it as an userland entrypoint for metric management

## Implementations

 * Abstract doctrine implementation
    * You have to implement entity finding, creating and sourcing
    * You can update receiving operation i.e with truncating or re-setting existing metrics
 
## Samples

Here is sample `ArrayStorage` implementation which stores metric into in-memory array 
ignoring tags on searching 

```php
<?php

use Lamoda\Metric\Common\Metric;
use Lamoda\Metric\Common\MetricSourceInterface;
use Lamoda\Metric\Storage\MutableMetricInterface;
use Lamoda\Metric\Storage\MetricStorageInterface;

final class ArrayStorage implements \IteratorAggregate, MetricStorageInterface
{
    private $metrics = [];
    
    public function receive(MetricSourceInterface $source): void {
        // optional, just example that metrics can dropped on receiving new source
        $this->metrics = []; 
        foreach ($source->getMetrics() as $metric) {
            $this->metrics[$metric->getName()] = $metric; 
        }
    }
    
    public function getMetrics(): \Traversable {
        return new ArrayIterator($this->metrics);
    }
    
    public function getIterator() {
        return $this->getMetrics();
    }
    
    public function findMetric(string $name, array $tags = []): ?MutableMetricInterface {
        return $this->metrics[$name] ?? null;
    }
    
    public function createMetric(string $name, float $value, array $tags = []): MutableMetricInterface {
        return $this->metrics[$name] = new Metric($name, $value, $tags);
    }
}
```

Having this storage you can create a generic mutator on top of it
```php
<?php

use Lamoda\Metric\Storage\StoredMetricMutator;

/** @var \Lamoda\Metric\Storage\MetricStorageInterface $storage */
$storage = new ArrayStorage();
$mutator = new StoredMetricMutator($storage);

$storage->createMetric('known_metric', 0.0);

// Here existent metric will be mutated and updated directly in the storage
$mutator->adjustMetricValue(1.0, 'known_metric', ['tags' => 'are ignored for that storage']);

// Here new metric would be created and put into the storage
$mutator->setMetricValue(241.0, 'unknown_metric', ['tags' => 'are still ignored']);
```

## Materializing metrics

As shown in above example, receiving metrics is a process of batch importing metrics into storage, 
i.e persisting to database, caching, etc.

Receiving could perform additional preparations on the storage, like wiping already stored metrics 
or setting them to pre-defined value (i.e zero).

Materializing is two-step receiving metrics

1. resolve every collected metric to get static values
2. put resolved metrics into the storage 

Generally, storage itself would perform metric resolution, but it stands better to do this process outside
of storage `receive` method execution in order to find resolution problems 
as early as possible (i.e not during locking transaction)

## General tips

* Avoid using the storage you receive in as a source of mutable metrics in order to avoid locking issues.

## Helpers

`StorageRegistry` can be used to hold named storages.

If you are using complex collector and storage configurations, you probably want to use `StorageRegistry`
and `CollectorRegistry` to manage named services. While using named services you can utilize `MaterializerHelper`
in order to perform materializing.
