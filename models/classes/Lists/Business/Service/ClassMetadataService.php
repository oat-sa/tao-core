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
use tao_helpers_form_elements_Textbox as TextBox;
use tao_helpers_form_elements_Textarea as TextArea;
use tao_helpers_form_elements_Htmlarea as HtmlArea;
use tao_helpers_form_elements_Radiobox as RadioBox;
use tao_helpers_form_elements_Checkbox as CheckBox;
use tao_helpers_form_elements_Combobox as ComboBox;
use tao_helpers_form_elements_xhtml_Searchdropdown as SearchDropDown;

class ClassMetadataService extends InjectionAwareService
{
    use OntologyAwareTrait;

    /** @var ValueCollectionService */
    private $valueCollectionService;

    public const SERVICE_ID = 'tao/ClassMetadataService';
    public const TEXT_WIDGETS = [
        TextBox::WIDGET_ID,
        TextArea::WIDGET_ID,
        HtmlArea::WIDGET_ID,
    ];

    public const LIST_WIDGETS = [
        RadioBox::WIDGET_ID,
        CheckBox::WIDGET_ID,
        ComboBox::WIDGET_ID,
        SearchDropDown::WIDGET_ID,
    ];

    public function __construct(ValueCollectionService $valueCollectionService)
    {
        $this->valueCollectionService = $valueCollectionService;
    }

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
            if (strpos($prop->getUri(), 'tao.rdf') === false) {
                continue;
            }

            if (!$this->isTextWidget($prop) && !$this->isListWidget($prop)) {
                continue;
            }

            array_push($properties, [
                'uri' => $prop->getUri(),
                'label' => $prop->getLabel(),
                'type' => $this->isListWidget($prop) ? 'list' : 'text',
                'values' => $this->isListWidget($prop) ? $this->getPropertyValues($prop) : null
            ]);
        }

        return $properties;
    }

    private function getPropertyValues(core_kernel_classes_Property $property): ?array
    {
        $values = [];
        $range = $property->getRange();

        $search = new ValueCollectionSearchInput(
            (new ValueCollectionSearchRequest())
                ->setValueCollectionUri($range->getUri())
        );
        $propertyCount = $this->valueCollectionService->count($search);

        if ($propertyCount > 5) {
            return null;
        }

        $valueCollection = $this->valueCollectionService->findAll($search);

        /** @var Value $value */
        foreach ($valueCollection as $value) {
            array_push($values, $value->getLabel());
        }

        return $values;
    }

    private function isTextWidget(core_kernel_classes_Property $property): bool
    {
        return in_array($property->getWidget()->getUri(), self::TEXT_WIDGETS);
    }

    private function isListWidget(core_kernel_classes_Property $property): bool
    {
        return in_array($property->getWidget()->getUri(), self::LIST_WIDGETS);
    }
}
