<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Validator;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

class RequestValidationException extends \Exception
{
    private ConstraintViolationListInterface $violations;

    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }

    public function __construct(
        ConstraintViolationListInterface $violations,
        $message = '',
        $code = 0,
        Throwable $previous = null
    ) {
        $this->violations = $violations;
        parent::__construct($message, $code, $previous);
    }
}
