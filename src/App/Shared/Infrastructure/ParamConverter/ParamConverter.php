<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\ParamConverter;

use App\Shared\Infrastructure\Validator\RequestValidationException;
use App\Shared\Infrastructure\Validator\Validable;
use App\Shared\Infrastructure\Validator\Validator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter as ParamConverterConfig;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class ParamConverter implements ParamConverterInterface
{
    private SerializerInterface $serializer;
    protected Validator $validator;

    public function __construct(
        SerializerInterface $serializer,
        Validator $validator,
    ) {
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    /**
     * @throws RequestValidationException
     */
    public function apply(
        Request $request,
        ParamConverterConfig $configuration
    ): bool {
        try {
            if ($request->getContent()) {
                /** @var Validable $command */
                $command = $this->serializer->deserialize($request->getContent(), $configuration->getClass(), 'json', [
                    AbstractNormalizer::DEFAULT_CONSTRUCTOR_ARGUMENTS => [
                        $configuration->getClass() => ['actorId' => 0],
                    ]
                ]);
            } else {
                $commandClass = $configuration->getClass();
                $command = new $commandClass();
            }
        } catch (NotNormalizableValueException $exception) {
            $message = $exception->getMessage();
            $violation = new ConstraintViolation($message, null, [], $exception->getTrace()[0]['args'][1] ?? '',
                $exception->getTrace()[0]['args'][1] ?? '', $exception->getTrace()[0]['args'][2] ?? '');
            $collection = new ConstraintViolationList([$violation]);
            throw new RequestValidationException($collection);
        }

        $reflected = new \ReflectionObject($command);

        foreach ($request->attributes->all() as $varName => $value) {
            if ($reflected->hasProperty($varName)) {
                $type = $reflected->getProperty($varName)->getType();
                $typeName = "";
                // since PHP8 there is ReflectionUnionType, check this code when upgrading
                if ($type instanceof \ReflectionNamedType) {
                    $typeName = $type->getName();
                }
                switch ($typeName) {
                    case 'int':
                        $value = (int)$value;
                        break;
                    case 'string':
                        $value = (string)$value;
                        break;
                    case 'float':
                        $value = (float)$value;
                        break;
                    case 'bool':
                        $value = (bool)$value;
                        break;
                    default:
                        throw new \Exception('Can not find type to inject route param to command');
                }
                $command->$varName = $value;
            }
        }
        $this->validator->validate($command);
        $request->attributes->set('command', $command);

        return true;
    }

    public function supports(ParamConverterConfig $configuration): bool
    {
        return 'command' === $configuration->getName();
    }
}
