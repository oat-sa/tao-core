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

namespace oat\tao\model\Lists\Presentation\Web\Factory;

use core_kernel_classes_Property;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\Lists\Business\Domain\DependencyProperty;
use oat\tao\model\Lists\DataAccess\Repository\DependencyPropertyRepository;
use tao_helpers_form_FormElement;
use tao_helpers_form_FormFactory;

class DependencyPropertyFormFieldFactory extends ConfigurableService
{
    public function create(array $options): ?tao_helpers_form_FormElement
    {
        $index = $options['index'];

        /** @var core_kernel_classes_Property $property */
        $property = $options['property'];

        $collection = $this->getRepository()->findByProperty($property);

        if ($collection->count() === 0) {
            return null;
        }

        $element = tao_helpers_form_FormFactory::getElement("{$index}_dep-on-prop", 'Combobox');
        $element->addAttribute('class', 'property-depends-on property');
        $element->setDescription(__('Depends on property'));
        $element->setEmptyOption(' --- ' . __('select') . ' --- ');

        $options = [];

        /** @var DependencyProperty $prop */
        foreach ($collection as $prop) {
            $options[$prop->getUriEncoded()] = $prop->getLabel();
        }

        $element->setOptions($options);

        return $element;
    }

    private function getRepository(): DependencyPropertyRepository
    {
        return $this->getServiceLocator()->get(DependencyPropertyRepository::class);
    }
}
