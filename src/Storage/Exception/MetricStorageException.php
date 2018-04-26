<?php

namespace Lamoda\Metric\Storage\Exception;

class MetricStorageException extends \OutOfBoundsException
{
    public static function becauseUnknownKeyInStorage(string $key): self
    {
        return new self(sprintf('Metric "%s" does not exist or is not adjustable', $key));
    }
}
