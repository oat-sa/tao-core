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
 * Copyright (c) 2021-2025 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\test\unit\user\implementation;

use oat\generis\model\data\Ontology;
use oat\generis\model\GenerisRdf;
use oat\generis\test\OntologyMockTrait;
use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\oatbox\user\UserTimezoneServiceInterface;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\user\implementation\UserSettings;
use oat\tao\model\user\implementation\UserSettingsService;
use core_kernel_classes_Resource;
use core_kernel_persistence_smoothsql_SmoothModel;
use oat\tao\model\user\UserSettingsInterface;
use PHPUnit\Framework\MockObject\MockObject;
use tao_models_classes_UserService;

class UserSettingsServiceTest extends TestCase
{
    use OntologyMockTrait;
    use ServiceManagerMockTrait;

    private Ontology|core_kernel_persistence_smoothsql_SmoothModel $ontologyMock;
    private UserSettingsService $sut;
    private UserSettings|MockObject $userSettings;
    private tao_models_classes_UserService|MockObject $userService;
    private FeatureFlagCheckerInterface|MockObject $featureFlagChecker;

    protected function setUp(): void
    {
        $userTimezoneService = $this->getUserTimezoneServiceMock();
        $this->userSettings = $this->createMock(UserSettings::class);
        $this->userService = $this->createMock(tao_models_classes_UserService::class);
        $this->featureFlagChecker = $this->createMock(FeatureFlagCheckerInterface::class);

        $this->sut = new UserSettingsService(
            $userTimezoneService,
            $this->getOntologyMock(),
            $this->userService,
            $this->featureFlagChecker
        );
    }

    /**
     * @dataProvider getDataProvider
     */
    public function testGet(
        UserSettings $expected,
        core_kernel_classes_Resource $user
    ): void {
        $settings = $this->sut->get($user);

        $this->assertEquals($expected->getTimezone(), $settings->getTimezone());
        $this->assertEquals($expected->getDataLanguageCode(), $settings->getDataLanguageCode());
        $this->assertEquals($expected->getUILanguageCode(), $settings->getUILanguageCode());
    }

    /**
     * @dataProvider getDataProvider
     */
    public function testGetCurrentUserSettings(
        UserSettings $expected,
        core_kernel_classes_Resource $user,
        bool $isSolarDesignEnabled
    ): void {
        $this->userService->method('getCurrentUser')->willReturn($user);

        $this->featureFlagChecker
            ->method('isEnabled')
            ->with(FeatureFlagCheckerInterface::FEATURE_FLAG_SOLAR_DESIGN_ENABLED)
            ->willReturn($isSolarDesignEnabled);

        $result = $this->sut->getCurrentUserSettings();

        $this->assertEquals($expected->getTimezone(), $result->getTimezone());
        $this->assertEquals($expected->getDataLanguageCode(), $result->getDataLanguageCode());
        $this->assertEquals($expected->getUILanguageCode(), $result->getUILanguageCode());

        $this->assertEquals(
            $expected->getSetting(UserSettingsInterface::TIMEZONE),
            $result->getSetting(UserSettingsInterface::TIMEZONE)
        );
        $this->assertEquals(
            $expected->getSetting(UserSettingsInterface::UI_LANGUAGE_CODE),
            $result->getSetting(UserSettingsInterface::UI_LANGUAGE_CODE)
        );
        $this->assertEquals(
            $expected->getSetting(UserSettingsInterface::DATA_LANGUAGE_CODE),
            $result->getSetting(UserSettingsInterface::DATA_LANGUAGE_CODE)
        );
        $this->assertEquals(
            $expected->getSetting(UserSettingsInterface::INTERFACE_MODE),
            $result->getSetting(UserSettingsInterface::INTERFACE_MODE)
        );
    }

    public function getDataProvider(): array
    {
        return [
            'Settings for a user with no timezone' => [
                'expected' => $this->createUserSetting(
                    null,
                    null,
                    'Europe/Luxembourg',
                    GenerisRdf::PROPERTY_USER_INTERFACE_MODE_SIMPLE
                ),
                'user' => $this->getUserMock(),
                'isSolarDesignEnabled' => true,
            ],
            'Settings for a user with timezone set' => [
                'expected' => $this->createUserSetting(
                    null,
                    null,
                    'Europe/Madrid',
                    GenerisRdf::PROPERTY_USER_INTERFACE_MODE_SIMPLE
                ),
                'user' => $this->getUserMock(null, null, 'Europe/Madrid'),
                'isSolarDesignEnabled' => true,
            ],
            'Settings for a user with UI language set' => [
                'expected' => $this->createUserSetting(
                    'uiLang',
                    null,
                    'Europe/Madrid',
                    GenerisRdf::PROPERTY_USER_INTERFACE_MODE_SIMPLE
                ),
                'user' => $this->getUserMock('uiLang', null, 'Europe/Madrid'),
                'isSolarDesignEnabled' => true,
            ],
            'Settings for a user with data language set' => [
                'expected' => $this->createUserSetting(
                    null,
                    'defLang',
                    'Europe/Madrid',
                    GenerisRdf::PROPERTY_USER_INTERFACE_MODE_SIMPLE
                ),
                'user' => $this->getUserMock(null, 'defLang', 'Europe/Madrid'),
                'isSolarDesignEnabled' => true,
            ],
            'Settings for a user with UI and data language set' => [
                'expected' => $this->createUserSetting(
                    'uiLang',
                    'defLang',
                    'Europe/Madrid',
                    GenerisRdf::PROPERTY_USER_INTERFACE_MODE_SIMPLE
                ),
                'user' => $this->getUserMock('uiLang', 'defLang', 'Europe/Madrid'),
                'isSolarDesignEnabled' => true,
            ],
            'Settings for a user with interface mode' => [
                'expected' => $this->createUserSetting(
                    null,
                    null,
                    'Europe/Madrid',
                    GenerisRdf::PROPERTY_USER_INTERFACE_MODE_ADVANCED
                ),
                'user' => $this->getUserMock(
                    null,
                    null,
                    'Europe/Madrid',
                    GenerisRdf::PROPERTY_USER_INTERFACE_MODE_ADVANCED
                ),
                'isSolarDesignEnabled' => true,
            ],
            'Settings for a user with interface mode when solar design disabled' => [
                'expected' => $this->createUserSetting(
                    null,
                    null,
                    'Europe/Madrid'
                ),
                'user' => $this->getUserMock(
                    null,
                    null,
                    'Europe/Madrid',
                    GenerisRdf::PROPERTY_USER_INTERFACE_MODE_ADVANCED
                ),
                'isSolarDesignEnabled' => false,
            ],
        ];
    }

    private function createUserSetting(
        string $uiLanguageUri = null,
        string $defLangUri = null,
        string $userTimezone = null,
        string $userInterfaceMode = null
    ): UserSettingsInterface {
        $userSettings = new UserSettings($userTimezone);

        return $userSettings->setSetting(UserSettingsInterface::INTERFACE_MODE, $userInterfaceMode)
            ->setSetting(UserSettingsInterface::UI_LANGUAGE_CODE, $uiLanguageUri)
            ->setSetting(UserSettingsInterface::DATA_LANGUAGE_CODE, $defLangUri)
            ->setSetting(UserSettingsInterface::TIMEZONE, $userTimezone);
    }

    private function getUserMock(
        string $uiLanguageUri = null,
        string $defLangUri = null,
        string $userTimezone = null,
        string $userInterfaceMode = null
    ): core_kernel_classes_Resource {
        $props = [];
        if (!empty($uiLanguageUri)) {
            $props[GenerisRdf::PROPERTY_USER_UILG] = [
                $this->getOntologyMock()->getResource($uiLanguageUri),
            ];
        }
        if (!empty($defLangUri)) {
            $props[GenerisRdf::PROPERTY_USER_DEFLG] = [
                $this->getOntologyMock()->getResource($defLangUri),
            ];
        }
        if (!empty($userTimezone)) {
            $props[GenerisRdf::PROPERTY_USER_TIMEZONE] = [
                new \core_kernel_classes_Literal($userTimezone),
            ];
        }
        if (!empty($userInterfaceMode)) {
            $props[GenerisRdf::PROPERTY_USER_INTERFACE_MODE] = [
                $this->getOntologyMock()->getResource($userInterfaceMode)
            ];
        }

        $user = $this->createMock(core_kernel_classes_Resource::class);
        $user
            ->method('getUri')
            ->willReturn('http://www.tao.lu/Ontologies/TAO.rdf#User');
        $user
            ->method('getModel')
            ->willReturn($this->getOntologyMock());
        $user
            ->method('getPropertiesValues')
            ->with([
                $this->getOntologyMock()->getProperty(GenerisRdf::PROPERTY_USER_UILG),
                $this->getOntologyMock()->getProperty(GenerisRdf::PROPERTY_USER_DEFLG),
                $this->getOntologyMock()->getProperty(GenerisRdf::PROPERTY_USER_TIMEZONE),
                $this->getOntologyMock()->getProperty(GenerisRdf::PROPERTY_USER_INTERFACE_MODE)
            ])
            ->willReturn($props);

        return $user;
    }

    private function getUserTimezoneServiceMock(): UserTimezoneServiceInterface
    {
        $serviceMock = $this->createMock(UserTimezoneServiceInterface::class);
        $serviceMock
            ->method('getDefaultTimezone')
            ->willReturn('Europe/Luxembourg');

        $serviceMock
            ->method('isUserTimezoneEnabled')
            ->willReturn(true);

        return $serviceMock;
    }
}
