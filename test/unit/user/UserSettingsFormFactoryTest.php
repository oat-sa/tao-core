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

namespace oat\tao\test\unit\user;

use oat\generis\model\OntologyRdf;
use oat\generis\test\OntologyMockTrait;
use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\oatbox\user\UserLanguageServiceInterface;
use oat\tao\model\user\implementation\UserSettings;
use oat\tao\model\user\UserSettingsFormFactory;
use tao_models_classes_LanguageService;
use core_kernel_classes_Resource;
use core_kernel_persistence_smoothsql_SmoothModel;
use tao_actions_form_UserSettings;
use tao_helpers_form_FormContainer;

class UserSettingsFormFactoryTest extends TestCase
{
    use OntologyMockTrait;
    use ServiceManagerMockTrait;

    /** @var UserSettingsFormFactory */
    private $sut;

    /** @var UserSettings */
    private $userSettings;

    /** @var core_kernel_persistence_smoothsql_SmoothModel */
    private $ontologyMock;

    /**
     * @var tao_models_classes_LanguageService
     */
    private $languageService;

    protected function setUp(): void
    {
        $this->ontologyMock = $this->getOntologyMock();
        $this->languageService = $this->getLanguageServiceMock();
        $this->userSettings = $this->createMock(UserSettings::class);

        $this->sut = new UserSettingsFormFactory($this->languageService);
    }

    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(
        string $expectedTimezone,
        string $expectedUiLanguage,
        ?string $expectedDataLanguage,
        ?string $defaultUiLanguage,
        string $userTimezone,
        ?string $userUiLanguage,
        ?string $userDataLanguage
    ): void {
        $this->userSettings
            ->method('getUILanguageCode')
            ->willReturn($userUiLanguage);
        $this->userSettings
            ->method('getDataLanguageCode')
            ->willReturn($userDataLanguage);
        $this->userSettings
            ->method('getTimezone')
            ->willReturn($userTimezone);

        // Just testing options and fields that would be used to instantiate
        // tao_actions_form_UserSettings, avoiding the complications related
        // with mocking the base FormContainer and WidgetRegistry classes.
        $options = $this->sut->createFormOptions();
        $fields = $this->sut->createFormFields($this->userSettings, $defaultUiLanguage);

        $this->assertEquals($expectedUiLanguage, $fields['ui_lang'] ?? null);
        $this->assertEquals($expectedDataLanguage, $fields['data_lang'] ?? null);
        $this->assertEquals($expectedTimezone, $fields['timezone']);

        $this->assertSame(
            $this->languageService,
            $options[tao_actions_form_UserSettings::OPTION_LANGUAGE_SERVICE]
        );
        $this->assertEquals(
            true,
            $options[tao_helpers_form_FormContainer::CSRF_PROTECTION_OPTION]
        );
    }

    public function createDataProvider(): array
    {
        return [
            'Having all values set' => [
                'expectedTimezone' => 'Europe/Luxembourg',
                'expectedUiLanguage' => 'en-US',
                'expectedDataLanguage' => 'en-US',

                'defaultUiLanguage' => 'en-US',

                'userTimezone' => '	Europe/Luxembourg',
                'userUiLanguage' => 'en-US',
                'userDataLanguage' => 'en-US',
            ],
            'Having no default UI language' => [
                'expectedTimezone' => 'Europe/Luxembourg',
                'expectedUiLanguage' => 'en-US',
                'expectedDataLanguage' => 'en-US',

                'defaultUiLanguage' => null,
                'userTimezone' => '	Europe/Luxembourg',
                'userUiLanguage' => 'en-US',
                'userDataLanguage' => 'en-US',
            ],
            'Having no user UI language' => [
                'expectedTimezone' => 'Europe/Luxembourg',
                'expectedUiLanguage' => 'fr-FR',
                'expectedDataLanguage' => 'en-US',

                'defaultUiLanguage' => 'fr-FR',
                'userTimezone' => '	Europe/Luxembourg',
                'userUiLanguage' => null,
                'userDataLanguage' => 'en-US',
            ],
            'Having no data language' => [
                'expectedTimezone' => 'Europe/Luxembourg',
                'expectedUiLanguage' => 'de-LU',
                'expectedDataLanguage' => null,

                'defaultUiLanguage' => 'fr-FR',
                'userTimezone' => '	Europe/Luxembourg',
                'userUiLanguage' => 'de-LU',
                'userDataLanguage' => null,
            ],
        ];
    }

    private function getLanguageServiceMock(): tao_models_classes_LanguageService
    {
        $serviceMock = $this->createMock(tao_models_classes_LanguageService::class);
        $serviceMock
            ->method('getAvailableLanguagesByUsage')
            ->willReturn([$this->getLanguage('English', 'en-US')]);

        return $serviceMock;
    }

    private function getUserLanguageServiceMock(): UserLanguageServiceInterface
    {
        $serviceMock = $this->createMock(UserLanguageServiceInterface::class);
        $serviceMock
            ->method('isDataLanguageEnabled')
            ->willReturn(true);

        return $serviceMock;
    }

    private function getLanguage(string $name, string $code): core_kernel_classes_Resource
    {
        $language = $this->createMock(core_kernel_classes_Resource::class);
        $language
            ->method('getUri')
            ->willReturn('http://www.tao.lu/Ontologies/TAO.rdf#Languages');
        $language
            ->method('getModel')
            ->willReturn($this->ontologyMock);
        $language
            ->method('getLabel')
            ->willReturn($name);
        $language
            ->method('getProperty')
            ->with(OntologyRdf::RDF_VALUE)
            ->willReturn($code);

        return $language;
    }
}
