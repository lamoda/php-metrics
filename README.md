# Lamoda metric responder

[![Build Status](https://github.com/lamoda/php-metrics/workflows/Tests/badge.svg?branch=master)](https://github.com/lamoda/php-metrics/workflows/Tests/badge.svg?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/lamoda/php-metrics/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/lamoda/php-metrics/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/lamoda/php-metrics/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/lamoda/php-metrics/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/lamoda/php-metrics/badges/build.png?b=master)](https://scrutinizer-ci.com/g/lamoda/php-metrics/build-status/master)
[![Latest Stable Version](https://poser.pugx.org/lamoda/metrics/v/stable)](https://packagist.org/packages/lamoda/metrics)

## Features

* Metric responder with lazy sourcing
* Multiple metric response formats
  * [Telegraf `JSON`](https://github.com/influxdata/telegraf/tree/master/plugins/inputs/httpjson)
  * [Prometheus exporter](https://prometheus.io/docs/instrumenting/writing_exporters/)
* Symfony bundle [optional]
* Doctrine and redis/predis integration out of the box

## Installation

* install with composer

```sh
composer require lamoda/metrics:^2.0
```

## Usage examples

* [Collecting metrics](doc/examples/01_collect.md)
* [Storing metrics](doc/examples/02_store.md)
* [Responding metrics](doc/examples/03_respond.md)
* [Symfony integration](src/MetricBundle/Resources/docs/integration.md)

## Main terms

* **Metric** is a named value, representing system running state, health check or cumulative measurement, optionally tagged

* **Metric response** is a set of metrics collected for the fixed moment of time, formatted for single input format
* **Metric responder** is a http endpoint used to render collected metrics into suitable web response format

* **Metrics** can be **sourced** in the terms of dynamic spawning for collection generation
  * The general advice is to have metrics as lazy as possible, resolving to the fixed at the time of generation response
  * Usually **Source** is implemented as an instance of `\Traversable` object to embed into collection

Metric are generally of two types:
* **Precomputed** metrics are usually generated outside of responding process, like counters
* **Runtime** metrics are generated on call, representing the current state of the system

From the point of metric responding the difference between types is nominal since PHP has share nothing architecture and most stored values should
be obtained from storage in any case (and thus waste some caller time), so
in general **Precomputed** metrics are just very fast **Runtime** metrics

But from the point of metric storing **Precomputed** metrics have more significant difference - some of them
can be retrieved from metric storage for update

* **Mutable** metric is the **Metric**, which can be updated with either with some delta or with absolute value according to
  business rules:
  * Counters
  * Accumulated total
  * Balance

* **Precomputed** metrics MAY NOT be **Mutable** since precomputing can be part of
  the responder performance optimization process and there is no sense in adjusting such value as it is overwritten
  during metric computation

* **Mutable Metric Storage** is the general storage interface which allows end user to mutate some metric by name and tags.
  It's depends on internal storage implementation, what happens if the metric is not found. If the storage allows
  dynamic metric generation - new metric would be stored silently with given value

## Supplementary terms

* **Collector** is the generic class serving the metric **Source** to other parts of the library
    * **Responder** to render
    * **Storage** receiver to cache them
    * Debug utilities for metric profiling and inspection

  In general **Collector** could just keep preconfigured **Source** or retrieve data from other sources (API, DB, Cache)

* **Materializing** is the process of resolving single metric **Collector** into precomputed metric **Source**
and storing it in a resolved form for fast access to some **Storage** (Cache, DB, etc)

* **Storage** is the metric storing driver. It is responsible for the following actions
    * Create new metric instance by its primary parts - name, value and tags
    * Find metric by name and tags
    * Work as a **Source** of metrics
    * Work as a receiver accepting **Source** to be materialized in it

## Extending

See [extending chapter](doc/extending.md)

## Development

### Running tests
```yaml
composer install
vendor/bin/phpunit
```
