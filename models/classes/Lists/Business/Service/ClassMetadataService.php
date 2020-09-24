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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 *
 * @author Sergei Mikhailov <sergei.mikhailov@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\model\Lists\Business\Service;

use core_kernel_classes_Class;
use core_kernel_classes_Property;
use oat\tao\model\Lists\Business\Domain\Value;
use oat\tao\model\Lists\Business\Domain\ValueCollectionSearchRequest;
use oat\tao\model\Lists\Business\Input\ClassMetadataSearchInput;
use oat\tao\model\Lists\Business\Input\ValueCollectionSearchInput;
use oat\tao\model\service\InjectionAwareService;
use oat\generis\model\OntologyAwareTrait;
use oat\tao\model\TaoOntology;

class ClassMetadataService extends InjectionAwareService
{
    use OntologyAwareTrait;

    public const SERVICE_ID = 'tao/ClassMetadataService';

    public function findAll(ClassMetadataSearchInput $input): array
    {
        /** @var core_kernel_classes_Class $class */
        $class = $this->getClass($input->getSearchRequest()->getClassUri());

        if (!$class->isClass()) {
            return [];
        }

        return $this->fillNodes([], $class);
    }

    private function fillNodes(
        array $node,
        core_kernel_classes_Class $currentClass,
        core_kernel_classes_Class $parentClass = null
    ): array {
        $subClasses = $currentClass->getSubClasses();

        if (count($subClasses)) {
            array_push ($node, [
                'class' => $currentClass->getUri(),
                'label' => $currentClass->getLabel(),
                'parent-class' => $parentClass !== null ? $parentClass->getUri() : null,
                'metadata' => $this->getClassMetadata($currentClass)
            ]);

            foreach ($subClasses as $subClass) {
                $node = $this->fillNodes($node, $subClass, $currentClass);
            }
        } else {
            array_push ($node, [
                'class' => $currentClass->getUri(),
                'label' => $currentClass->getLabel(),
                'parent-class' => $parentClass->getUri(),
                'metadata' => $this->getClassMetadata($currentClass)
            ]);
        }

        return $node;
    }

    private function getClassMetadata(core_kernel_classes_Class $class): array
    {
        $properties = [];

        /** @var core_kernel_classes_Property $prop */
        foreach ($class->getProperties(true) as $prop) {
            $range = $prop->getRange();
            $isList = $this->isList($range);

            array_push($properties, [
                'uri' => $prop->getUri(),
                'label' => $prop->getLabel(),
                'type' => $prop->getWidget() ? $prop->getWidget()->getLabel() : null,//$isList ? 'list' : 'text',
                'values' => $isList ? $this->getPropertyValues($range) : null
            ]);
        }

        return $properties;
    }

    private function getPropertyValues($range): array
    {
        $values = [];
        $valueCollectionService = $this->getServiceLocator()->get(ValueCollectionService::class);

        $valueCollection = $valueCollectionService->findAll(
            new ValueCollectionSearchInput(
                (new ValueCollectionSearchRequest())
                    ->setValueCollectionUri($range->getUri())
            )
        );

        /** @var Value $value */
        foreach ($valueCollection as $value) {
            array_push($values, $value->getLabel());
        }

        return $values;
    }

    private function isList($range): bool
    {
        if (!$range->isClass()) {
            return false;
        }

        return $range->isSubClassOf(
            new core_kernel_classes_Class(TaoOntology::CLASS_URI_LIST)
        );
    }
}
