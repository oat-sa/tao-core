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
use oat\oatbox\log\LoggerAwareTrait;
use oat\oatbox\user\UserTimezoneServiceInterface;
use oat\tao\model\RdfObjectMapper\RdfObjectMapper;
use oat\tao\model\RdfObjectMapper\TargetTypes\UserSettingsMappedType;
use oat\tao\model\user\UserSettingsInterface;
use oat\tao\model\user\UserSettingsServiceInterface;
use core_kernel_classes_Resource;
use Psr\Log\LoggerAwareInterface;

class UserSettingsService implements UserSettingsServiceInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var Ontology */
    private $ontology;

    /** @var string */
    private $defaultTimeZone;

    private $objectMapper;

    public function __construct(UserTimezoneServiceInterface $userTimezoneService, Ontology $ontology)
    {
        $this->defaultTimeZone = $userTimezoneService->getDefaultTimezone();
        $this->ontology = $ontology;

        // @fixme Inject the mapper
        $this->objectMapper = new RdfObjectMapper(
            $this->getLogger()
        );
    }

    public function get(core_kernel_classes_Resource $user): UserSettingsInterface
    {
        return $this->objectMapper->mapResource($user, UserSettingsMappedType::class);
    }

    public function previousGetImplementation(core_kernel_classes_Resource $user): UserSettingsInterface
    {
        $props = $user->getPropertiesValues(
            [
                $this->ontology->getProperty(GenerisRdf::PROPERTY_USER_UILG),
                $this->ontology->getProperty(GenerisRdf::PROPERTY_USER_DEFLG),
                $this->ontology->getProperty(GenerisRdf::PROPERTY_USER_TIMEZONE)
            ]
        );

        if (!empty($props[GenerisRdf::PROPERTY_USER_UILG])) {
            $uiLanguageCode = current($props[GenerisRdf::PROPERTY_USER_UILG])->getUri();
        }

        if (!empty($props[GenerisRdf::PROPERTY_USER_DEFLG])) {
            $dataLanguageCode = current($props[GenerisRdf::PROPERTY_USER_DEFLG])->getUri();
        }

        if (!empty($props[GenerisRdf::PROPERTY_USER_TIMEZONE])) {
            $timezone = (string) current($props[GenerisRdf::PROPERTY_USER_TIMEZONE]);
        }

        return new UserSettings(
            $timezone ?? $this->defaultTimeZone,
            $uiLanguageCode ?? null,
            $dataLanguageCode ?? null
        );
    }
}
