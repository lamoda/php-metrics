# Configuration reference

```yaml
lamoda_metrics:
    metrics:
        sources:

            # Prototype
            name:

                # Type of the source
                type:                 service # One of "doctrine"; "service"; "composite"

                # Mark this source as metric storage. Will perform adjustable metrics resolution against it
                storage:              false

                # Source service identifier
                id:                   null

                # Entity class
                entity:               Lamoda\MetricResponder\MetricInterface

                # Metric services
                metrics:              []
    groups:
        sources:

            # Prototype
            name:

                # Type of the source
                type:                 service # One of "doctrine"; "service"; "merging"

                # Mark this source as metric storage. Will perform adjustable metrics resolution against it
                storage:              false

                # Service identifier
                id:                   null

                # Entity class
                entity:               Lamoda\MetricResponder\MetricGroupInterface

                # Group services
                groups:               []
        custom:

            # Prototype
            name:

                # Group tags
                tags:                 []

                # Mark this source as metric storage. Will perform adjustable metrics resolution against it
                storage:              false

                # Metric source names or service ids
                metric_sources:       []

                # Additional metric services for this group (also populated with tag)
                metric_services:      []
    responders:

        # Prototype
        name:
            enabled:              false

            # Responder route path
            path:                 null # Example: "/telegraf". Defaults to "/$name"

            # Response factory service ID
            response_factory:     null # Example: lamoda_metrics.response_factory.telegraf. Defaults to "lamoda_metrics.response_factory.$name"
            sources:              []
            groups:               []
```
