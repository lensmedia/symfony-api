<?php

namespace Lens\Bundle\ApiBundle\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CustomContextHttpException extends HttpException
{
    public function __construct(
        private readonly array $context = [],
        int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR,
        string $message = '',
        \Throwable $previous = null,
        array $headers = [],
        int $code = 0,
    ) {
        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
