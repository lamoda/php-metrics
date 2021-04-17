<?php

declare(strict_types=1);

namespace Lamoda\Metric\Adapters\Redis;

/** @internal */
final class MetricDto
{
    /** @var string */
    public $name;
    /** @var float */
    public $value;
    /** @var array */
    public $tags;

    public function __construct(string $name, float $value, array $tags)
    {
        $this->name = $name;
        $this->value = $value;
        $this->tags = $tags;
    }
}
