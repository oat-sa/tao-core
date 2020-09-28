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
use oat\tao\model\Lists\Business\Domain\ClassMetadata;
use oat\tao\model\Lists\Business\Domain\Metadata;
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
use tao_helpers_form_elements_xhtml_Searchtextbox as SearchTextBox;

class ClassMetadataService extends InjectionAwareService
{
    use OntologyAwareTrait;

    /** @var ValueCollectionService */
    private $valueCollectionService;

    /** @var int */
    private $maxListSize;

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
        SearchTextBox::WIDGET_ID,
    ];
    public const DATA_TYPE_LIST = 'list';
    public const DATA_TYPE_TEXT = 'text';

    private const BASE_LIST_ITEMS_URI = '/tao/PropertyValues/get?propertyUri=%s&subject=';


    public function __construct(ValueCollectionService $valueCollectionService)
    {
        $this->valueCollectionService = $valueCollectionService;
    }

    public function findAll(ClassMetadataSearchInput $input): array
    {
        /** @var core_kernel_classes_Class $class */
        $this->maxListSize = $input->getSearchRequest()->getMaxListSize();
        $class = $this->getClass($input->getSearchRequest()->getClassUri());

        if (!$class->isClass()) {
            return [];
        }

        return $this->fillData([], $class);
    }

    private function fillData(
        array $data,
        core_kernel_classes_Class $currentClass,
        core_kernel_classes_Class $parentClass = null
    ): array {
        $subClasses = $currentClass->getSubClasses();

        if (count($subClasses)) {
            $classMetadata = (new ClassMetadata())
                ->setClass($currentClass->getUri())
                ->setLabel($currentClass->getLabel())
                ->setParentClass($parentClass !== null ? $parentClass->getUri() : null)
                ->setMetaData($this->getClassMetadata($currentClass));


            array_push($data, $classMetadata);

            foreach ($subClasses as $subClass) {
                $data = $this->fillData($data, $subClass, $currentClass);
            }
        } else {
            $classMetadata = (new ClassMetadata())
                ->setClass($currentClass->getUri())
                ->setLabel($currentClass->getLabel())
                ->setParentClass($parentClass !== null ? $parentClass->getUri() : null)
                ->setMetaData($this->getClassMetadata($currentClass));

            array_push($data, $classMetadata);
        }

        return $data;
    }

    private function getClassMetadata(core_kernel_classes_Class $class): array
    {
        $properties = [];

        foreach ($class->getProperties(true) as $property) {
            if (strpos($property->getUri(), 'ontologies/tao.rdf') === false) {
                continue;
            }

            if (!$this->isTextWidget($property) && !$this->isListWidget($property)) {
                continue;
            }

            $values = $this->isListWidget($property) ? $this->getPropertyValues($property) : null;
            $uri = $this->isListWidget($property) && !$values ? $this->getListItemsUri($property) : null;

            $metadata = (new Metadata())
                ->setLabel($property->getLabel())
                ->setType($this->isListWidget($property) ? self::DATA_TYPE_LIST : self::DATA_TYPE_TEXT)
                ->setValues($values)
                ->setUri($uri);


            array_push($properties, $metadata);
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
        $propertyValuesCount = $this->valueCollectionService->count($search);

        if ($propertyValuesCount > $this->maxListSize) {
            return null;
        }

        $valueCollection = $this->valueCollectionService->findAll($search);

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

    private function getListItemsUri(core_kernel_classes_Property $property): string
    {
        return sprintf(self::BASE_LIST_ITEMS_URI, urlencode($property->getUri()));
    }
}
