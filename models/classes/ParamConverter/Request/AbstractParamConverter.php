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

namespace oat\tao\model\ParamConverter\Request;

use Throwable;
use oat\tao\model\HttpFoundation\Request\RequestInterface;
use oat\tao\model\ParamConverter\Configuration\ParamConverter;
use oat\tao\model\ParamConverter\Context\ObjectFactoryContext;
use oat\tao\model\ParamConverter\Factory\ObjectFactoryInterface;

abstract class AbstractParamConverter implements ParamConverterInterface
{
    /** @var ObjectFactoryInterface */
    private $objectFactory;

    public function __construct(ObjectFactoryInterface $objectFactory)
    {
        $this->objectFactory = $objectFactory;
    }

    public function getPriority(): int
    {
        return 0;
    }

    public function apply(RequestInterface $request, ParamConverter $configuration): bool
    {
        try {
            $options = $configuration->getOptions();
            $data = $this->getData($request, $options);
            $object = $this->createObject($data, $configuration->getClass(), $options);

            $converted = $request->getAttribute(self::ATTRIBUTE_CONVERTED, []);
            $converted[$configuration->getName()] = $object;

            $request->setAttribute(self::ATTRIBUTE_CONVERTED, $converted);
        } catch (Throwable $exception) {
            return false;
        }

        return true;
    }

    public function supports(ParamConverter $configuration): bool
    {
        return $configuration->getClass() !== null && $configuration->getConverter() === $this->getName();
    }

    abstract protected function getData(RequestInterface $request, array $options): array;

    private function createObject(array $data, string $class, array $options): object
    {
        $rule = $options[ParamConverter::OPTION_CREATION_RULE] ?? null;
        $context = new ObjectFactoryContext(
            [
                ObjectFactoryContext::PARAM_CLASS => $class,
                ObjectFactoryContext::PARAM_DATA => $data,
            ]
        );

        if ($rule === ParamConverter::RULE_CREATE) {
            $object = $this->objectFactory->create($context);
        } else {
            $object = $this->objectFactory->deserialize($context);
        }

        return $object;
    }
}
