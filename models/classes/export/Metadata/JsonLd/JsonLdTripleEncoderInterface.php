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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

declare(strict_types=1);

namespace oat\tao\model\export\Metadata\JsonLd;

use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use core_kernel_classes_Triple;

interface JsonLdTripleEncoderInterface
{
    public const RDF_TYPE = 'http://www.w3.org/2000/01/rdf-schema#type';
    public const RDF_VALUE = 'http://www.w3.org/2000/01/rdf-schema#value';
    public const RDF_LABEL = 'http://www.w3.org/2000/01/rdf-schema#label';

    public const CONTEXT_VALUE = 'value';
    public const CONTEXT_TYPE = 'type';
    public const CONTEXT_ALIAS = 'alias';
    public const CONTEXT_LABEL = 'label';

    public function encode(
        array $dataToEncode,
        core_kernel_classes_Triple $triple,
        core_kernel_classes_Property $property = null,
        core_kernel_classes_Resource $widget = null
    ): array;

    public function isWidgetSupported(string $widgetUri): bool;
}
