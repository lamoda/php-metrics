<?php

namespace Lamoda\Metric\Storage\Exception;

class ReceiverException extends \RuntimeException
{
    public static function becauseOfStorageFailure(\Throwable $e): self
    {
        return new self(
            'Failed to persist metrics into upstream store', $e->getCode(), $e
        );
    }
}
