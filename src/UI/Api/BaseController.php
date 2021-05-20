<?php

declare(strict_types=1);

namespace UI\Api;

use App\Shared\Infrastructure\Validator\Validator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class BaseController extends AbstractController
{
    public function __construct(
        protected DenormalizerInterface $denormalizer,
        protected SerializerInterface $serializer,
        Validator $validator
    ) {

    }
}
