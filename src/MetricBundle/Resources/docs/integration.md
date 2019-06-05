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

In general you do not have to use `FrameworkBundle`, but then you'll have to deal 
with routing of metric responders by yourself.

Configure some metrics:
```yaml
services:
  custom_tagged_metric:
    class: Lamoda\Metric\Common\Metric
    arguments:
    - "tagged_metric"
    - 2.0
    - [{ name: lamoda_telegraf_metric, group: heartbeat }]

  custom_metric:
    class: Lamoda\Metric\Common\Metric
    arguments:
    - "custom_metric"
    - 1.0

  custom_metric_for_composite:
    class: Lamoda\Metric\Common\Metric
    arguments:
    - "custom_metric_for_composite"
    - 2.2
```

Configure bundle (sample from tests):

```yaml
lamoda_metrics:
  sources:
    doctrine_entity_source:
      type: storage
      storage: doctrine
    composite_source:
      type: composite
      metrics:
       - custom_metric
       - custom_metric_for_composite

  collectors:
    raw_sources:
      type: sources
      sources:
        - composite_source
      metric_services:
        - custom_tagged_metric
      default_tags: {collector: raw}

    doctrine:
      type: sources
      sources:
        - doctrine_entity_source
      default_tags: {collector: doctrine}

  storages:
    doctrine:
      type: service
      mutator: true
      id: test.doctrine_metric_storage

  responders:
    telegraf_json:
      enabled: true
      collector: raw_sources
      format_options:
        group_by_tags:
        - type
        propagate_tags:
        - type

    custom_telegraf:
      enabled: true
      collector: raw_sources
      response_factory: telegraf_json
      format_options:
        group_by_tags: []
        propagate_tags:
        - type
      path: /custom_telegraf

    prometheus:
      enabled: true
      collector: raw_sources
      format_options:
        prefix: metrics_
      path: /prometheus

```

Configure routing
```yaml
_lamoda_metrics:
  resource: .
  type: lamoda_metrics
  prefix: /metrics/
```

### Configuration

See [configuration reference](reference.md)
