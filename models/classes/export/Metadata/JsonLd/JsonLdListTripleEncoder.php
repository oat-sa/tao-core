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

namespace oat\tao\model\export\Metadata\JsonLd;

use core_kernel_classes_Class;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use core_kernel_classes_Triple;
use InvalidArgumentException;
use oat\tao\helpers\form\elements\xhtml\SearchDropdown;
use oat\tao\helpers\form\elements\xhtml\SearchTextBox;
use oat\tao\model\Lists\Business\Domain\ValueCollectionSearchRequest;
use oat\tao\model\Lists\Business\Input\ValueCollectionSearchInput;
use oat\tao\model\Lists\Business\Service\ValueCollectionService;
use oat\tao\model\Lists\Business\Specification\LocalListClassSpecification;
use oat\tao\model\Lists\Business\Specification\RemoteListClassSpecification;
use tao_helpers_form_elements_Checkbox;
use tao_helpers_form_elements_Combobox;
use tao_helpers_form_elements_Radiobox;
use tao_helpers_form_elements_Treebox;

class JsonLdListTripleEncoder implements JsonLdTripleEncoderInterface
{
    /** @var ValueCollectionService */
    private $valueCollectionService;

    /** @var RemoteListClassSpecification */
    private $remoteListClassSpecification;

    /** @var LocalListClassSpecification */
    private $localListClassSpecification;

    public function __construct(
        ValueCollectionService $valueCollectionService,
        RemoteListClassSpecification $remoteListClassSpecification,
        LocalListClassSpecification $localListClassSpecification
    ) {
        $this->valueCollectionService = $valueCollectionService;
        $this->remoteListClassSpecification = $remoteListClassSpecification;
        $this->localListClassSpecification = $localListClassSpecification;
    }

    public function encode(
        array $dataToEncode,
        core_kernel_classes_Triple $triple,
        core_kernel_classes_Property $property = null,
        core_kernel_classes_Resource $widget = null
    ): array {
        if ($property === null || $widget === null) {
            throw new InvalidArgumentException('The parameters $property and $widget are required');
        }

        $propertyRange = $property->getRange();

        if (!$propertyRange instanceof core_kernel_classes_Class) {
            return $dataToEncode;
        }

        $isRemoteList = $this->remoteListClassSpecification->isSatisfiedBy($propertyRange);
        $isLocalList = $this->localListClassSpecification->isSatisfiedBy($propertyRange);

        if (!$isRemoteList && !$isLocalList) {
            return $dataToEncode;
        }

        $key = $this->getMetadataKey($triple, $dataToEncode);

        if (empty($dataToEncode[$key][self::CONTEXT_TYPE])) {
            $dataToEncode[$key] = [
                self::CONTEXT_TYPE => $widget->getUri(),
                self::CONTEXT_ALIAS => $property->getAlias(),
                self::CONTEXT_LABEL => $property->getLabel(),
                self::CONTEXT_VALUE => [],
            ];
        }

        $request = $this->prepareSearch($propertyRange, $property, $triple, $isRemoteList);

        $value = $this->valueCollectionService
            ->findAll(new ValueCollectionSearchInput($request))
            ->extractValueByUri($triple->object);

        $dataToEncode[$key][self::CONTEXT_VALUE][] = [
            self::CONTEXT_VALUE => $triple->object,
            self::CONTEXT_LABEL => $value ? $value->getLabel() : null,
        ];

        return $dataToEncode;
    }

    private function prepareSearch(
        core_kernel_classes_Class $propertyRange,
        core_kernel_classes_Property $property,
        core_kernel_classes_Triple $triple,
        bool $isRemoteList
    ): ValueCollectionSearchRequest {
        $request = new ValueCollectionSearchRequest();

        if ($isRemoteList) {
            $request->setValueCollectionUri($propertyRange->getUri());
            $request->setUris($triple->object);

            return $request;
        }

        $request->setPropertyUri($property->getUri());

        return $request;
    }

    private function getMetadataKey(core_kernel_classes_Triple $triple, array $dataToEncode): ?string
    {
        $context = (array)($dataToEncode['@context'] ?? []);

        return array_flip($context)[$triple->predicate] ?? null;
    }

    public function isWidgetSupported(string $widgetUri): bool
    {
        return in_array(
            $widgetUri,
            [
                tao_helpers_form_elements_Radiobox::WIDGET_ID,
                tao_helpers_form_elements_Treebox::WIDGET_ID,
                tao_helpers_form_elements_Combobox::WIDGET_ID,
                tao_helpers_form_elements_Checkbox::WIDGET_ID,
                SearchTextBox::WIDGET_ID,
                SearchDropdown::WIDGET_ID,
            ],
            true
        );
    }
}
