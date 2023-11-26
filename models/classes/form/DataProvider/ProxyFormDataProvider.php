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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Gabriel Felipe Soares <gabriel.felipe.soares@taotesting.com>
 */

namespace oat\tao\model\form\DataProvider;

use core_kernel_classes_Class;
use core_kernel_classes_Property;

class ProxyFormDataProvider implements FormDataProviderInterface
{
    /** @var array<FormDataProviderInterface> */
    private array $dataProviders;

    public function __construct(array $dataProviders)
    {
        $this->dataProviders = $dataProviders;
    }

    public function getClassProperties(core_kernel_classes_Class $class, core_kernel_classes_Class $topClass): array
    {
        return $this->callMethod(__FUNCTION__, func_get_args());
    }

    public function getDataToFeedProperty(core_kernel_classes_Property $property): array
    {
        return $this->callMethod(__FUNCTION__, func_get_args());
    }

    public function getDescriptionFromTranslatedPropertyLabel(core_kernel_classes_Property $property, string $language): ?string
    {
        return $this->callMethod(__FUNCTION__, func_get_args());
    }

    public function fetchFormData(string $classUri, string $topClassUri, string $elementUri, string $language): void
    {
        $this->callMethod(__FUNCTION__, func_get_args());
    }

    public function isPropertyList(core_kernel_classes_Property $property): bool
    {
        return $this->callMethod(__FUNCTION__, func_get_args());
    }

    public function getPropertyListElementOptions(core_kernel_classes_Property $property, ?core_kernel_classes_Property $parentProperty, $instance): array
    {
        return $this->callMethod(__FUNCTION__, func_get_args());
    }

    public function getPropertyNotListElementOptions(core_kernel_classes_Property $property): array
    {
        return $this->callMethod(__FUNCTION__, func_get_args());
    }

    public function getPropertyValidators(core_kernel_classes_Property $property): array
    {
        return $this->callMethod(__FUNCTION__, func_get_args());
    }

    public function getPropertyInstanceValues(core_kernel_classes_Property $property, $instance, $element): array
    {
        return $this->callMethod(__FUNCTION__, func_get_args());
    }

    public function getPropertyGUIOrder(core_kernel_classes_Property $property): array
    {
        return $this->callMethod(__FUNCTION__, func_get_args());
    }

    /**
     * @return mixed
     */
    private function callMethod(string $method, array $args)
    {
        foreach($this->dataProviders as $dataProvider) {
            try {
                return $dataProvider->$method(...$args);
            } catch (DataProviderException $e) {
                continue;
            }
        }

        throw new \RuntimeException(sprintf('Unable to call %s method', $method));
    }
}
