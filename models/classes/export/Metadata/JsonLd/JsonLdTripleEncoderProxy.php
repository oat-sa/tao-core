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
use oat\tao\helpers\form\elements\xhtml\SearchDropdown;
use oat\tao\helpers\form\elements\xhtml\SearchTextBox;
use oat\tao\model\export\Metadata\JsonLd\JsonLdTripleEncoderInterface;
use tao_helpers_form_elements_Calendar;
use tao_helpers_form_elements_Checkbox;
use tao_helpers_form_elements_Combobox;
use tao_helpers_form_elements_Hiddenbox;
use tao_helpers_form_elements_Htmlarea;
use tao_helpers_form_elements_Radiobox;
use tao_helpers_form_elements_Textarea;
use tao_helpers_form_elements_Textbox;
use tao_helpers_form_elements_Treebox;

class JsonLdTripleEncoderProxy implements JsonLdTripleEncoderInterface
{
    private const ALLOWED_WIDGETS = [
        tao_helpers_form_elements_Textbox::WIDGET_ID,
        tao_helpers_form_elements_Textarea::WIDGET_ID,
        tao_helpers_form_elements_Htmlarea::WIDGET_ID,
        tao_helpers_form_elements_Radiobox::WIDGET_ID,
        tao_helpers_form_elements_Treebox::WIDGET_ID,
        tao_helpers_form_elements_Combobox::WIDGET_ID,
        tao_helpers_form_elements_Checkbox::WIDGET_ID,
        SearchTextBox::WIDGET_ID,
        SearchDropdown::WIDGET_ID,
        tao_helpers_form_elements_Calendar::WIDGET_ID,
        tao_helpers_form_elements_Hiddenbox::WIDGET_ID
    ];

    /** @var Ontology */
    private $ontology;

    /** @var JsonLdTripleEncoderInterface[] */
    private $encoders = [];

    /** @var array */
    private $allowedWidgets;

    public function __construct(Ontology $ontology, array $allowedWidgets = [])
    {
        $this->ontology = $ontology;
        $this->allowedWidgets = empty($allowedWidgets) ? self::ALLOWED_WIDGETS : $allowedWidgets;
    }

    public function addEncoder(JsonLdTripleEncoderInterface $encoder): void
    {
        $this->encoders[] = $encoder;
    }

    public function encode(core_kernel_classes_Triple $triple, array $dataToEncode): array
    {
        $property = $this->ontology->getProperty($triple->predicate);
        $widgetUri = $property->getWidget() ? $property->getWidget()->getUri() : null;

        if ($widgetUri === null) {
            return $dataToEncode;
        }

        foreach ($this->encoders as $encoder) {
            if ($encoder->isWidgetSupported($widgetUri)) {
                $dataToEncode = $encoder->encode($triple, $dataToEncode);
            }
        }

        return $dataToEncode;
    }

    public function isWidgetSupported(string $widgetUri): bool
    {
        return in_array($widgetUri, $this->allowedWidgets, true);
    }
}
