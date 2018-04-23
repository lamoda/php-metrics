# Lamoda metric responder

## Features

* Metric responder with grouping and lazy sourcing
* Multiple metric sources
  * Doctrine2 ORM source out of the box
* Multiple metric formatters
  * [Telegraf `httpjson`](https://github.com/influxdata/telegraf/tree/master/plugins/inputs/httpjson) 
  * [Prometheus exporters](https://prometheus.io/docs/instrumenting/writing_exporters/)
* Searchable metric storage
* Symfony bundle [opitonal]

## Installation

* install with composer

```sh
composer require lamoda/metrics:^1.0
```

## Terms

* **Metric** is a named value, representing system running state, health check or cumulative measurement
* **Group** is a collection of **Metrics** supplied with metadata
  * Metadata is `string[]` only, represented as tags at the moment

* **Metric response** is a collection of groups for the fixed moment of time, formatted for single input format
* **Metric responder** is a http endpoint used to render **Group** or collection of **Groups** 
  into suitable web response format

* **Metrics** and **Groups** can be **sourced** in the terms of dynamic spawning for collection generation
  * **Metric Sourcing** is the matter of **Group** implementation, allowing the **Group** to have any number of contained
  metrics, possibly changing between resolution
  * **Group Sourcing** is the optional responder configuration to feed it with dynamic collection of **Groups**, 
  possibly changing between generated responses
  * In general **Sourcing** is implemented as an instance of `\Traversable` object to embed into collection

Metric are generally of two types:
* **Precomputed** metrics are usually generated outside of responding process, like counters
* **Runtime** metrics are generated on call, representing the current state of the system

From the point of metric responding the difference between types is nominal since PHP has share nothing architecture and most stored values should
be obtained from storage in any case (and thus waste some caller time), so 
in general **Precomputed** metrics are just very fast **Runtime** metrics

But from the point of metric storing **Precomputed** metrics have more significant difference - some of them 
can be retrieved from metric storage for update

* **Adjustable** metric is the **Metric**, which can be differentially updated with some delta value according to
  business rules:
  * Counters
  * Accumulated total
  * Balance
  
* **Precomputed** metrics MAY NOT be **Adjustable** since precomputing can be part of 
  the responder performance optimization process and there is no sense in adjusting such value as it is overwritten
  during metric computation
  
* **Adjustable Metric Storage** is the general storage interface which allows end user to retrieve some metric by name
  for updating. It's depends on internal storage implementation, what happens if the metric is not found or 
  metric is found but it is not and **Adjustable** metric
  * Out-of-the-box implementation throws an exception
  * There is an abstract Doctrine2 ORM storage decorator, which allows to dynamically create 
    new **Adjustable** metric as a Doctrine entity if no suitable metric found 

## Standalone responder usage

* You can create own metric and source it to formatter as a single metric group. 
* You can source multiple groups at once
* You can group multiple metrics in one groups

```php
<?php

use Lamoda\MetricResponder\MetricGroup\CombinedMetricGroup;
use Lamoda\MetricResponder\MetricInterface;
use Lamoda\MetricResponder\GroupSource\ArrayMetricGroupSource;
use Lamoda\MetricResponder\ResponseFactory\TelegrafResponseFactory;

final class MyMetric implements MetricInterface {
    public function getName(): string {
        return 'duck_count';
    }
    
    public function resolve(): float {
        // some heavy calculations here
        
        return 241;
    }
}

/** @var MetricInterface $metric */
$metric = new MyMetric();

// General composite metric group to merge source an standalone metrics
$group = new CombinedMetricGroup('metric_group_1', ['tag1' => 'tag_value']);
$group->addMetric($metric);

// General iterable array group source
$source = new ArrayMetricGroupSource([$group]);

$factory = new TelegrafResponseFactory();

// PSR-7 Response 
$response = $factory->create($source);
```

## Standalone storage usage

Configure storage
```php
<?php    

use Lamoda\MetricInfra\MetricSource\DoctrineMetricSource;
use Lamoda\MetricStorage\AdjustableMetricStorageInterface
use Lamoda\MetricStorage\DelegatingMetricStorage;;

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

## Symfony integration

### Installation

Configure kernel class

```php
    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            // <...> More bundles 
            new LamodaMetricBundle(),
        ];
    }
    
```

In general you do not have to use `FrameworkBundle`, but then you'll have to deal with routing by yourself.


Configure some metrics and groups

```yaml
lamoda_metrics:
  metrics:
    sources:
      my_custom_metric_entity:
        type: doctrine
        entity: Lamoda\MetricBundle\Tests\Fixtures\Entity\Metric
        storage: true
      composite:
        type: composite
        metrics:
        - custom_metric

  groups:
    sources:
      doctrine_entity_source:
        type: doctrine
        entity: Lamoda\MetricBundle\Tests\Fixtures\Entity\MetricGroup
    custom:
      my_custom_group:
        tags: {type: custom}
        metric_sources:
          - my_custom_metric_entity
          - composite
        metric_services:
          - custom_metric
      heartbeat:
        tags: {type: heartbeat}

  responders:
    telegraf:
      enabled: true
      groups:
      - my_custom_group
      sources:
      - doctrine_entity_source
    custom_telegraf:
      enabled: true
      response_factory: lamoda_metrics.response_factory.json
      normalizer: lamoda_metrics.normalizer.telegraf
      path: /custom_telegraf
      groups:
      - my_custom_group
      sources:
      - doctrine_entity_source
```

Configure routing
```yaml
_lamoda_metrics:
  resource: .
  type: lamoda_metrics
  prefix: /metrics/
```

### Storage usage example

For Doctrine2-driven storage you can create a decorator by extending `AbstractDoctrineMetricStorage` passing the 
original storage and `ObjectManager` interface in order to automatically create new metrics

```php
<?php

use Lamoda\MetricInfra\Doctrine\AbstractDoctrineMetricStorage;
use Lamoda\MetricStorage\AdjustableMetricInterface;

final class DoctrineMetricStorage extends AbstractDoctrineMetricStorage
{
    /**
     * {@inheritdoc}
     */
    protected function instantiateEmptyMetric(string $key): AdjustableMetricInterface
    {
        return new Metric($key, 0);
    }
}
```

You can decorate concrete Doctrine storage delegate or the whole `lamoda_metrics.metric_storage` service

```yaml
services:
    app.metric_storage:
        class: AppBundle\Monitoring\DoctrineMetricStorage
        public: false
        decorates: 'lamoda_metrics.metric_storage'
        arguments:
            - '@app.metric_storage.inner'
            - "@doctrine.orm.sidecar_entity_manager"
```

You can mark any custom source to work as storage by marking it with `storage: true` config option. 
This will automatically decorate source with iterator introspecting decorator.

```yaml
lamoda_metrics:
  metrics:
    sources:
      my_custom_metric_entity:
        type: doctrine
        entity: Lamoda\MetricBundle\Tests\Fixtures\Entity\Metric
        storage: true
```

### Configuration

See [configuration reference](doc/symfony/reference.md)

## Extending

### Creating own response factory

Implement `ResponseFactoryInterface` which consumes the metric group source and produces PSR-7 Response

### Sourcing

One can implement and composition of metric and group sources implementing the following interfaces

 * `MetricGroupSourceInterface`  
 * `MetricGroupInterface`  
 * `MetricSourceInterface`  

These could provide any metrics, i.e FS stats, exec result, API calls, etc, 
represented with lazily wrapped `MetricInterface` implementation

## Current extensions

### Sources

 * `DoctrineMetricSource` provides metrics stored with Doctrine 2 ORM powered with DBAL. Atomic adjustments allowed
 * `DoctrineMetricGroupSource` provides groups stored with Doctrine 2 ORM

## Formatters

 * `TelegrafResponseFactory` creates telegraf httpjson compatible output, grouped
 * `PrometheusResponseFactory` creates prometheus compatible output  

## Development

### Running tests
```yaml
composer install
vendor/bin/phpunit
```
