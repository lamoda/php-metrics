<?php

namespace Lamoda\Metric\Storage\Exception;

class ReceiverException extends \RuntimeException
{
    public static function becauseOfStorageFailure(\Throwable $e)
    {
        return new static(
            'Failed to persist metrics into upstream store', $e->getCode(), $e
        );
    }
}
