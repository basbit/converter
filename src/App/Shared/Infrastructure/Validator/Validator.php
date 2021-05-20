<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Validator;

use Symfony\Component\Validator\Validator\ValidatorInterface;

class Validator
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @throws RequestValidationException
     */
    public function validate(Validable $validable): void
    {
        $violations = $this->validator->validate($validable);
        if ($violations->count()) {
            throw new RequestValidationException($violations);
        }
    }
}
