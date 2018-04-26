# Configuration reference

```yaml
lamoda_metrics:
    sources:

        # Prototype: Sources also can be configured as services via `lamoda_metrics.source` tag with `alias` attribute
        name:
            enabled:              true

            # Type of the source
            type:                 service # One of "doctrine"; "service"; "composite"; "storage"

            # Source service identifier [service]
            id:                   null

            # Entity class [doctrine]
            entity:               Lamoda\Metric\Common\MetricInterface

            # Metric services [composite]
            metrics:              []

            # Storage name [storage]
            storage:              null
    response_factories:

        # Prototype: Response factories also can be configured as services via `lamoda_metrics.response_factory` tag with `alias` attribute
        name:
            enabled:              true

            # Type of the factory
            type:                 service # One of "service"

            # Response factory service identifier [service]
            id:                   null
    responders:

        # Prototype
        name:
            enabled:              true

            # Responder route path. Defaults to /$name
            path:                 null # Example: /prometheus

            # Formatter options
            format_options:

                # Metrics prefix for responder
                prefix:               '' # Example: project_name_

                # Propagate tags to group [telegraf_json]
                propagate_tags:       []

                # Arrange metrics to groups according to tag value. Tag name goes to group name [telegraf_json]
                group_by_tags:        []

            # Response factory alias
            response_factory:     null # Example: prometheus

            # Collector alias
            collector:            ~ # Required
    storages:

        # Prototype: Storages also can be configured as services via `lamoda_metrics.storage` tag with `alias` attribute
        name:
            enabled:              true

            # Storage service ID [service]
            id:                   ~ # Example: Lamoda\Metric\Storage\MetricStorageInterface

            # Type of the storage
            type:                 service # One of "service"

            # Configure storage as default metric mutator
            mutator:              false
    collectors:

        # Prototype: Collectors also can be configured as services via `lamoda_metrics.collector` tag with `alias` attribute
        name:
            enabled:              true

            # Collector service ID
            id:                   null # Example: Lamoda\Metric\Collector\MetricCollectorInterface

            # Type of the collector
            type:                 service # One of "service"; "sources"; "merge"
            collectors:           []
            sources:              []
            metric_services:      []

            # Default tag values for metrics from this collector
            default_tags:         []

```
