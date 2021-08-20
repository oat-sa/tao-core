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

namespace oat\tao\model\Lists\Business\Service;

use core_kernel_classes_Class;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\tao\helpers\form\elements\xhtml\SearchDropdown;
use oat\tao\model\Lists\Business\Domain\Metadata;
use oat\tao\model\Lists\Business\Domain\MetadataCollection;
use oat\tao\model\Lists\Business\Domain\ValueCollectionSearchRequest;
use oat\tao\model\Lists\Business\Input\ValueCollectionSearchInput;
use tao_helpers_form_elements_Checkbox as CheckBox;
use tao_helpers_form_elements_Combobox as ComboBox;
use tao_helpers_form_elements_Htmlarea as HtmlArea;
use tao_helpers_form_elements_Radiobox as RadioBox;
use tao_helpers_form_elements_Textarea as TextArea;
use tao_helpers_form_elements_Textbox as TextBox;
use tao_helpers_form_elements_xhtml_Searchtextbox as SearchTextBox;

class GetClassMetadataValuesService extends ConfigurableService
{
    use OntologyAwareTrait;

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
        SearchDropdown::WIDGET_ID,
    ];
    public const DATA_TYPE_LIST = 'list';
    public const DATA_TYPE_TEXT = 'text';

    private const BASE_LIST_ITEMS_URI = '/tao/PropertyValues/get?propertyUri=%s';

    public function getByClassRecursive(
        core_kernel_classes_Class $class,
        int $maxListSize = 100
    ): MetadataCollection {
        return $this->getClassMetadata($class, $maxListSize, true);
    }

    public function getByClassExplicitly(
        core_kernel_classes_Class $class,
        int $maxListSize = 100
    ): MetadataCollection {
        return $this->getClassMetadata($class, $maxListSize, false);
    }

    private function getClassMetadata(
        core_kernel_classes_Class $class,
        int $maxListSize,
        bool $recursively = true
    ): MetadataCollection {
        $collection = new MetadataCollection();

        foreach ($class->getProperties($recursively) as $property) {
            if (!$this->isWidget($property)) {
                continue;
            }

            if (!$this->isTextWidget($property) && !$this->isListWidget($property)) {
                continue;
            }

            $values = $this->getPropertyValues($property, $maxListSize);
            $uri = $this->getListItemsUri($property, $values);

            $metadata = (new Metadata())
                ->setLabel($property->getLabel())
                ->setType($this->isListWidget($property) ? self::DATA_TYPE_LIST : self::DATA_TYPE_TEXT)
                ->setValues($values)
                ->setUri($uri)
                ->setPropertyUri($property->getUri());


            $collection->addMetadata($metadata);
        }

        return $collection;
    }

    private function getPropertyValues(core_kernel_classes_Property $property, int $maxListSize): ?array
    {
        if (!$this->isListWidget($property)) {
            return null;
        }

        $values = [];
        $range = $property->getRange();

        $search = new ValueCollectionSearchInput(
            (new ValueCollectionSearchRequest())
                ->setValueCollectionUri($range->getUri())
        );
        $propertyValuesCount = $this->getValueCollectionService()->count($search);

        if ($propertyValuesCount > $maxListSize) {
            return null;
        }

        $valueCollection = $this->getValueCollectionService()->findAll($search);

        foreach ($valueCollection as $value) {
            array_push($values, $value->getLabel());
        }

        return $values;
    }

    private function isTextWidget(core_kernel_classes_Property $property): bool
    {
        $widgetUri = $property->getWidget()->getUri();
        return ($widgetUri)
            ? in_array($widgetUri, self::TEXT_WIDGETS, true)
            : false;
    }

    private function isListWidget(core_kernel_classes_Property $property): bool
    {
        $widgetUri = $property->getWidget()->getUri();
        return ($widgetUri) ?
            in_array($widgetUri, self::LIST_WIDGETS, true)
            : false;
    }

    private function getListItemsUri(core_kernel_classes_Property $property, ?array $values): ?string
    {
        if (!$this->isListWidget($property) || $values) {
            return null;
        }

        return sprintf(self::BASE_LIST_ITEMS_URI, urlencode($property->getUri()));
    }

    private function isWidget(core_kernel_classes_Property $property): bool
    {
        return $property->getWidget() instanceof core_kernel_classes_Resource;
    }

    private function getValueCollectionService(): ValueCollectionService
    {
        return $this->getServiceLocator()->get(ValueCollectionService::SERVICE_ID);
    }
}
