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

use core_kernel_classes_Triple;
use oat\generis\model\data\Ontology;
use tao_helpers_form_elements_Calendar;
use tao_helpers_form_elements_Hiddenbox;
use tao_helpers_form_elements_Htmlarea;
use tao_helpers_form_elements_Textarea;
use tao_helpers_form_elements_Textbox;

class JsonLdBasicTripleEncoder implements JsonLdTripleEncoderInterface
{
    /** @var Ontology */
    private $ontology;

    public function __construct(Ontology $ontology)
    {
        $this->ontology = $ontology;
    }

    public function encode(core_kernel_classes_Triple $triple, array $dataToEncode): array
    {
        $property = $this->ontology->getProperty($triple->predicate);

        $key = $this->getMetadataKey($triple, $dataToEncode);

        $dataToEncode[$key] = [
            self::RDF_TYPE => $property->getWidget()->getUri(),
            self::RDF_VALUE => $triple->object,
        ];

        return $dataToEncode;
    }

    private function getMetadataKey(core_kernel_classes_Triple $triple, array $dataToEncode): ?string
    {
        return array_flip($dataToEncode['@context'] ?? [])[$triple->predicate] ?? null;
    }

    public function isWidgetSupported(string $widgetUri): bool
    {
        return in_array(
            $widgetUri,
            [
                tao_helpers_form_elements_Textbox::WIDGET_ID,
                tao_helpers_form_elements_Textarea::WIDGET_ID,
                tao_helpers_form_elements_Htmlarea::WIDGET_ID,
                tao_helpers_form_elements_Calendar::WIDGET_ID,
                tao_helpers_form_elements_Hiddenbox::WIDGET_ID
            ],
            true
        );
    }
}
