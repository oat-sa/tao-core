<?php

declare(strict_types=1);

namespace oat\tao\model\ParamConverter\Configuration;

use RuntimeException;

abstract class ConfigurationAnnotation
{
    public function __construct(array $values)
    {
        foreach ($values as $property => $value) {
            if ($this->setViaSetter($property, $value) || $this->setDirectly($property, $value)) {
                continue;
            }

            throw new RuntimeException(
                sprintf(
                    'Unknown property "%s" for annotation "@%s".',
                    $property,
                    static::class
                )
            );
        }
    }

    private function setViaSetter(string $property, $value): bool
    {
        $isSetterExists = method_exists($this, 'set' . $property);

        if ($isSetterExists) {
            $this->{'set' . $property}($value);
        }

        return $isSetterExists;
    }

    private function setDirectly(string $property, $value): bool
    {
        $isPropertyExists = property_exists($this, $property);

        if ($isPropertyExists) {
            $this->$property = $value;
        }

        return $isPropertyExists;
    }
}
