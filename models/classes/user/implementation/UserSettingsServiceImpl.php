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

namespace oat\tao\model\user\implementation;

use oat\generis\model\GenerisRdf;
use oat\tao\model\user\UserSettings;
use oat\tao\model\user\UserSettingsService;
use core_kernel_classes_Resource;

use Laminas\ServiceManager\ServiceLocatorAwareTrait;
use oat\generis\model\OntologyAwareTrait;

class UserSettingsServiceImpl implements UserSettingsService
{
    use ServiceLocatorAwareTrait;
    use OntologyAwareTrait;

    /** @var string */
    protected $defaultTimeZone;

    public function __construct(string $defaultTimeZone)
    {
        $this->defaultTimeZone = $defaultTimeZone;
    }

    public function getUserSettings(core_kernel_classes_Resource $user): UserSettings
    {
        $props = $user->getPropertiesValues(
            [
                $this->getProperty(GenerisRdf::PROPERTY_USER_UILG),
                $this->getProperty(GenerisRdf::PROPERTY_USER_DEFLG),
                $this->getProperty(GenerisRdf::PROPERTY_USER_TIMEZONE)
            ]
        );

        $builder = new UserSettingsBuilder($this->defaultTimeZone);

        if (!empty($props[GenerisRdf::PROPERTY_USER_UILG])) {
            $builder->withUILanguage(current($props[GenerisRdf::PROPERTY_USER_UILG])->getUri());
        }

        if (!empty($props[GenerisRdf::PROPERTY_USER_DEFLG])) {
            $builder->withDataLanguage(current($props[GenerisRdf::PROPERTY_USER_DEFLG])->getUri());
        }

        if (!empty($props[GenerisRdf::PROPERTY_USER_TIMEZONE])) {
            $builder->withTimezone(current($props[GenerisRdf::PROPERTY_USER_TIMEZONE]));
        }

        return $builder->build();
    }
}
