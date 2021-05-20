<?php


namespace App\Shared\Infrastructure\Traits;

use ReflectionClass;
use ReflectionProperty;

trait ObjectArrayConversionTrait
{
    public function setFromArray(array $properties): void
    {
        $reflection = new ReflectionClass($this);

        foreach ($properties as $property => $value) {
            if ($reflection->hasProperty($property)) {
                $property = $reflection->getProperty($property);

                if ($property instanceof ReflectionProperty) {
                    if ($property->getModifiers() !== 'public') {
                        $property->setAccessible(true);
                    }

                    $property->setValue($this, $value);
                }
            }
        }
    }

    public function toArray(bool $isRecursive = true): array
    {
        $result = [];

        foreach (get_object_vars($this) as $propertyName => $propertyValue) {
            if (is_null($propertyValue) || (is_array($propertyValue) && !$propertyValue)) {
                continue;
            }

            if ($isRecursive && is_object($propertyValue) && method_exists($propertyValue, 'toArray')) {
                $propertyValue = $this->cutCommonData($propertyValue->toArray());
            }

            $result[$propertyName] = $propertyValue;
        }

        return $this->cutCommonData($result);
    }

    protected function cutCommonData(array $data): array
    {
        $cutArray = [
            '__initializer__',
            '__cloner__',
            '__isInitialized__',
        ];

        foreach ($data as $key => $value) {
            if (empty($value) && in_array($key, $cutArray, true)) {
                unset($data[$key]);
            }
        }

        return $data;
    }

}
