<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\ParamConverter\Factory;

use ReflectionClass;
use InvalidArgumentException;
use oat\tao\model\Serializer\SerializerInterface;
use oat\tao\model\ParamConverter\Context\ObjectFactoryContextInterface;

class ObjectFactory implements ObjectFactoryInterface
{
    /** @var SerializerInterface */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function create(ObjectFactoryContextInterface $context): object
    {
        $constructorArgs = [];
        $data = $context->getData();

        $reflectionClass = new ReflectionClass($context->getClass());
        $constructor = $reflectionClass->getConstructor();

        if ($constructor) {
            foreach ($constructor->getParameters() as $constructorParameter) {
                $constructorParameterName = $constructorParameter->getName();

                if (array_key_exists($constructorParameterName, $data)) {
                    $constructorArgs[$constructorParameterName] = $data[$constructorParameterName];
                    unset($data[$constructorParameterName]);
                }
            }
        }

        $instance = $reflectionClass->newInstanceArgs($constructorArgs);

        foreach ($data as $queryParameter => $value) {
            if ($reflectionClass->hasMethod('set' . $queryParameter)) {
                $reflectionClass
                    ->getMethod('set' . $queryParameter)
                    ->invoke($instance, $value);
            } elseif ($reflectionClass->hasProperty($queryParameter)) {
                $reflectionClass->getProperty($queryParameter)->setValue($instance, $value);
            }
        }

        return $instance;
    }

    public function deserialize(ObjectFactoryContextInterface $context): object
    {
        $format = $context->getFormat();

        if ($format !== 'json') {
            throw new InvalidArgumentException('Currently, only the "json" format is supported.');
        }

        return $this->serializer->deserialize(
            json_encode($context->getData()),
            $context->getClass(),
            $format,
            $context->getContext()
        );
    }
}
