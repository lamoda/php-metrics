<?php

namespace Lamoda\MetricStorage\Exception;

class MetricStorageException extends \OutOfBoundsException
{
    public static function becauseUnknownKeyInStorage(string $key)
    {
        return new static(sprintf('Metric "%s" does not exist or not modifiable', $key));
    }
}
