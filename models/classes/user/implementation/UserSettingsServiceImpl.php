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

declare(strict_types=1);

namespace oat\tao\model\user\implementation;

use oat\generis\model\data\Ontology;
use oat\generis\model\GenerisRdf;
use oat\tao\model\user\UserSettings;
use oat\tao\model\user\UserSettingsService;
use core_kernel_classes_Resource;

class UserSettingsServiceImpl implements UserSettingsService
{
    /** @var Ontology */
    private $ontology;

    /** @var string */
    private $defaultTimeZone;

    public function __construct(string $defaultTimeZone, Ontology $ontology)
    {
        $this->defaultTimeZone = $defaultTimeZone;
        $this->ontology = $ontology;
    }

    public function getUserSettings(core_kernel_classes_Resource $user): UserSettings
    {
        $props = $user->getPropertiesValues(
            [
                $this->ontology->getProperty(GenerisRdf::PROPERTY_USER_UILG),
                $this->ontology->getProperty(GenerisRdf::PROPERTY_USER_DEFLG),
                $this->ontology->getProperty(GenerisRdf::PROPERTY_USER_TIMEZONE)
            ]
        );

        $builder = new UserSettingsBuilder($this->defaultTimeZone);

        if (!empty($props[GenerisRdf::PROPERTY_USER_UILG])) {
            $builder->setUILanguage(current($props[GenerisRdf::PROPERTY_USER_UILG])->getUri());
        }

        if (!empty($props[GenerisRdf::PROPERTY_USER_DEFLG])) {
            $builder->setDataLanguage(current($props[GenerisRdf::PROPERTY_USER_DEFLG])->getUri());
        }

        if (!empty($props[GenerisRdf::PROPERTY_USER_TIMEZONE])) {
            $builder->setTimezone(current($props[GenerisRdf::PROPERTY_USER_TIMEZONE]));
        }

        return $builder->build();
    }
}
