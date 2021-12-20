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

namespace oat\tao\test\unit\models\classes\service;

use oat\tao\model\search\SearchProxy;
use oat\tao\model\search\SearchQuery;
use oat\tao\model\TaoOntology;
use oat\tao\model\user\GenerisUserService;
use oat\generis\test\TestCase;
use oat\tao\model\user\implementation\UserSettings;
use oat\tao\model\user\UserSettingsFormFactory;

class UserSettingsServiceTest extends TestCase
{
    /** @var UserSettingsFormFactory */
    private $sut;

    /** @var UserSettings */
    private $userSettings;

    public function setUp(): void
    {
        $this->sut = new UserSettingsFormFactory();
        $this->userSettings = $this->createMock(UserSettings::class);
    }

    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(
        string $defaultUiLanguage,
        string $userUiLanguage,
        string $dataLanguage,
        string $timezone
    ): void {
        $this->userSettings
            ->method('getUILanguageCode')
            ->willReturn($userUiLanguage);
        $this->userSettings
            ->method('getDataLanguageCode')
            ->willReturn($dataLanguage);
        $this->userSettings
            ->method('getTimezone')
            ->willReturn($timezone);


    }

    public function createDataProvider(): array
    {
        return [
            'foo' => [
                'defaultUiLanguage' => 'en-US', // Can be null as well
                'userUiLanguage' => 'en-US', // Can be null as well
                'dataLanguage' => 'en-US', // Can be null as well
                'timezone' => '	Europe/Luxembourg'
            ]
        ];
    }
}
