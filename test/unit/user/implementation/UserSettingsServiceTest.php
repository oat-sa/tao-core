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

namespace oat\tao\test\unit\models\classes\service;

use oat\generis\model\data\Ontology;
use oat\generis\model\GenerisRdf;
use oat\generis\test\OntologyMockTrait;
use oat\generis\test\TestCase;
use oat\oatbox\user\UserTimezoneServiceInterface;
use oat\tao\model\user\implementation\UserSettings;
use oat\tao\model\user\implementation\UserSettingsService;
use core_kernel_classes_Resource;
use core_kernel_persistence_smoothsql_SmoothModel;
use PHPUnit\Framework\MockObject\MockObject;

class UserSettingsServiceTest extends TestCase
{
    use OntologyMockTrait;

    /** @var UserTimezoneServiceInterface */
    private $userTimezoneService;

    /** @var Ontology|core_kernel_persistence_smoothsql_SmoothModel */
    private $ontologyMock;

    /** @var UserSettingsService */
    private $sut;

    /** @var UserSettings|MockObject */
    private $userSettings;

    public function setUp(): void
    {
        $this->userTimezoneService = $this->getUserTimezoneServiceMock();
        $this->userSettings = $this->createMock(UserSettings::class);

        $this->sut = new UserSettingsService(
            $this->userTimezoneService,
            $this->getOntologyMock()
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

    public function getDataProvider(): array
    {
        return [
            'Settings for a user with no timezone' => [
                'expected' => new UserSettings('Europe/Luxembourg', null, null),
                'user' => $this->getUserMock('', '', null),
            ],
            'Settings for a user with timezone set' => [
                'expected' => new UserSettings('Europe/Madrid', null, null),
                'user' => $this->getUserMock('', '', 'Europe/Madrid'),
            ],
            'Settings for a user with UI language set' => [
                'expected' => new UserSettings('Europe/Madrid', 'uiLang', null),
                'user' => $this->getUserMock('uiLang', '', 'Europe/Madrid'),
            ],
            'Settings for a user with data language set' => [
                'expected' => new UserSettings('Europe/Madrid', null, 'defLang'),
                'user' => $this->getUserMock('', 'defLang', 'Europe/Madrid'),
            ],
            'Settings for a user with UI and data language set' => [
                'expected' => new UserSettings('Europe/Madrid', 'uiLang', 'defLang'),
                'user' => $this->getUserMock('uiLang', 'defLang', 'Europe/Madrid'),
            ],
        ];
    }

    private function getUserMock(
        string $uiLanguageUri,
        string $defLangUri,
        ?string $userTimezone
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
                $this->getOntologyMock()->getProperty(GenerisRdf::PROPERTY_USER_TIMEZONE)
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
