## Standalone storage usage

Configure storage
```php
<?php    
/** @var \Doctrine\Common\Persistence\ManagerRegistry $manager */

// Doctrine storage
$doctrineStorage = new DoctrineMetricSource($manager, MyMetric::class);

// Custom storage
$customStorage = new class implements AdjustableMetricStorageInterface { /* ... */};

// Delegating chain 
$storage = new DelegatingMetricStorage([$doctrineStorage, $customStorage]);

// Use storage
$storage->hasAdjustableMetric('metric_name');

try {
    $storage->getAdjustableMetric('unknown_metric_name');
} catch (\Lamoda\MetricStorage\Exception\MetricStorageException $e) {
    //... 
}

```

You can pass storage as a dependency

```php
<?php 

use Lamoda\MetricStorage\AdjustableMetricStorageInterface;

final class SomeImportantHandler
{
    /** @var AdjustableMetricStorageInterface */
    private $storage;
    
    public function __construct(AdjustableMetricStorageInterface $storage) {
        $this->storage = $storage;
    }
    
    public function doWeirdStuff()
    {
        // ...
       
        $this->storage->getAdjustableMetric('weird_stuff_runs')->adjust(1);
    }
} 
```

With this snippet metric named `weird_stuff_runs` if it exists. Will throw `MetricStorageException` 
if metric was not found and storage is not able to create and initialize it automatically.
