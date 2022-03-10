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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA.
 */

namespace oat\tao\model\RdfObjectMapper\TargetTypes;

use oat\generis\model\GenerisRdf;
use oat\tao\model\user\UserSettingsInterface;

require_once __DIR__ . '/../Annotation/RdfAttributeMapping.php';
require_once __DIR__ . '/../Annotation/RdfResourceAttributeMapping.php';
require_once __DIR__ . '/../Annotation/RdfResourceAttributeType.php';

use oat\tao\model\RdfObjectMapper\Annotation\RdfAttributeMapping;
use oat\tao\model\RdfObjectMapper\Annotation\RdfResourceAttributeMapping;
use oat\tao\model\RdfObjectMapper\Annotation\RdfResourceAttributeType;


/**
 * Example object type with RDF mapping annotations
 */
class UserSettingsMappedType implements UserSettingsInterface
{
    //#[RdfResourceAttributeMapping(RdfResourceAttributeMapping::URI)]
    /** @RdfResourceAttributeMapping(type = RdfResourceAttributeType::URI) */
    private /*string*/ $userUri;

    //#[RdfResourceAttributeMapping(RdfResourceAttributeMapping::LABEL)]
    /** @RdfResourceAttributeMapping(type = RdfResourceAttributeType::LABEL) */
    private /*string*/ $userLabel;

    //#[RdfResourceAttributeMapping(RdfResourceAttributeMapping::COMMENT)]
    /** @RdfResourceAttributeMapping(type = RdfResourceAttributeType::COMMENT) */
    private /*string*/ $userComment;

    /*#[RdfAttributeMapping(
        GenerisRdf::PROPERTY_USER_UILG,
        'resource',

        // field returned for non-literal properties.
        // Can be
        // - 'alias' (string)
        // - 'range' (core_kernel_classes_ContainerCollection, calls $property->getRange())
        // - 'resource' (complete resource instance)
        //      - 'uri' (resource URI as string)
        'uri'
    )]*/

    /** @RdfAttributeMapping(
     *     propertyUri = GenerisRdf::PROPERTY_USER_UILG,
     *     attributeType = "resource",
     *     mappedField = "uri")
     */
    private /*?string*/ $uiLanguageCode;

    /**
     * @RdfAttributeMapping(
     *     propertyUri = GenerisRdf::PROPERTY_USER_DEFLG,
     *     attributeType = "resource",
     *     mappedField = "uri")
     */
    private /*?string*/ $dataLanguage;

    /**
     * property returned as a core_kernel_classes_Literal instance,
     * will be converted to string
     * @todo Use RdfResourceAttributeType (or a new class) constants for
     *       attributeType
     * @RdfAttributeMapping(
     *     GenerisRdf::PROPERTY_USER_TIMEZONE, attributeType = "literal")
     */
    private /*string*/ $timezone;

    public function getUri(): string
    {
        return $this->userUri;
    }

    public function getLabel(): string
    {
        return $this->userLabel;
    }

    public function getComment(): string
    {
        return $this->userComment;
    }

    public function getUILanguageCode(): ?string
    {
        return $this->uiLanguageCode;
    }

    public function getDataLanguageCode(): ?string
    {
        return $this->dataLanguage;
    }

    public function getTimezone(): string
    {
        return $this->timezone;
    }
}
