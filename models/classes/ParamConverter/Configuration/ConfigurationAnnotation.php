<?php

declare(strict_types=1);

namespace oat\tao\model\ParamConverter\Configuration;

use RuntimeException;

abstract class ConfigurationAnnotation
{
    public function __construct(array $values)
    {
        foreach ($values as $property => $value) {
            $setter = 'set' . ucfirst($property);

            if (method_exists($this, $setter)) {
                $this->$setter($value);

                continue;
            } elseif (property_exists($this, $property)) {
                $this->$property = $values;

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
}
