# Symfony integration

## Installation

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
