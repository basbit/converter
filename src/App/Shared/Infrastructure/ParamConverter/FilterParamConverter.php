<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\ParamConverter;

use api\Exception\EnumDeserializationException;
use App\Shared\Infrastructure\ReadModel\FilterInterface;
use ReflectionClass;
use ReflectionException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter as ParamConverterConfig;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class FilterParamConverter implements ParamConverterInterface
{
    private DenormalizerInterface $denormalizer;

    public function __construct(DenormalizerInterface $denormalizer)
    {
        $this->denormalizer = $denormalizer;
    }

    /**
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function apply(Request $request, ParamConverterConfig $configuration)
    {
        $filterClass = $configuration->getClass();

        $filter = new $filterClass();
        /** @var FilterInterface $filter */
        $filter = $this->denormalizer->denormalize(
            $request->query->get('filter') ?: [],
            $filterClass, 'array', [
            'object_to_populate' => $filter,
        ]);

        $request->attributes->set($configuration->getName(), $filter);

        return true;
    }

    /**
     * @throws ReflectionException
     */
    public function supports(ParamConverterConfig $configuration)
    {
        if (!$configuration->getClass()) {
            return false;
        }
        $reflected = new ReflectionClass($configuration->getClass());

        return $reflected->implementsInterface(FilterInterface::class);
    }
}
