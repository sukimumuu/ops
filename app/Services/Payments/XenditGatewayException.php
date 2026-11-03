<?php

namespace App\Services\Payments;

use RuntimeException;
use Throwable;

class XenditGatewayException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly bool $retryable,
        public readonly ?int $statusCode = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
