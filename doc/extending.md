# Extending
## Creating own response factory

Implement `ResponseFactoryInterface` which consumes the metric group source and produces PSR-7 Response

## Sourcing

These could provide any metrics, i.e FS stats, exec result, API calls, etc, 
represented with lazily wrapped `MetricInterface` implementation

# Current extensions

## Storage

 * `AbstractDoctrineStorage` provides metrics stored with Doctrine 2 ORM powered with DBAL. Atomic adjustments allowed
 
## Response formats

 * `TelegrafJsonResponseFactory` creates telegraf JSON compatible output with the following options
   * `propagate_tags` allows you to display metric tags on the group level. In case of different tag values the one rendered later will win
   * `group_by_tags` allows you tou create nested groups according to tag value. Usually you want also propagate these tags
 * `PrometheusResponseFactory` creates prometheus compatible output  
