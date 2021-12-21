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

use oat\generis\model\OntologyRdf;
use oat\generis\test\OntologyMockTrait;
use oat\generis\test\TestCase;
use oat\oatbox\service\ServiceManager;
use oat\oatbox\user\UserLanguageServiceInterface;
use oat\oatbox\user\UserTimezoneServiceInterface;
use oat\tao\helpers\form\WidgetRegistry;
use oat\tao\model\user\implementation\UserSettings;
use oat\tao\model\user\UserSettingsFormFactory;
use tao_models_classes_LanguageService;
use core_kernel_classes_Resource;
use core_kernel_classes_Class;
use core_kernel_persistence_smoothsql_SmoothModel;
use ReflectionProperty;

/**
 * @runClassInSeparateProcess since this test changes global state
 */
class UserSettingsFormFactoryTest extends TestCase
{
    use OntologyMockTrait;

    /** @var UserSettingsFormFactory */
    private $sut;

    /** @var UserSettings */
    private $userSettings;

    /** @var core_kernel_persistence_smoothsql_SmoothModel */
    private $ontologyMock;

    private $cache = [];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Overriding WidgetRegistry::$widgetCache initial value since it would
        // be initialized by calling common_cache_FileCache::singleton() if it
        // is null, and tao_helpers_form_FormFactory makes use of it via static
        // calls.
        //
        // In turn, the form factory is used by tao_helpers_form_FormContainer,
        // which is extended by tao_actions_form_UserSettings.
        //
        $reflector = new ReflectionProperty(WidgetRegistry::class, 'widgetCache');
        $reflector->setAccessible(true);
        $reflector->setValue(null, []);
    }

    public static function tearDownAfterClass(): void
    {
        $reflector = new \ReflectionProperty(WidgetRegistry::class, 'widgetCache');
        $reflector->setAccessible(true);
        $reflector->setValue(null);

        parent::tearDownAfterClass();
    }

    public function setUp(): void
    {
        $this->ontologyMock = $this->getOntologyMock();

        // Inject services used by the WidgetRegistry
        $serviceManagerMock = $this->getServiceLocatorMock([
            UserLanguageServiceInterface::class => $this->getUserLanguageServiceMock(),
            UserTimezoneServiceInterface::SERVICE_ID => $this->getUserTimezoneServiceMock(),
        ]);

        ServiceManager::setServiceManager($serviceManagerMock);

        $this->userSettings = $this->createMock(UserSettings::class);
        $this->sut = new UserSettingsFormFactory($this->getLanguageServiceMock());
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

        $form = $this->sut->create($this->userSettings, $defaultUiLanguage);

        $this->assertEquals($expectedUiLanguage, $form->getValue('ui_lang'));
        $this->assertEquals($expectedDataLanguage, $form->getValue('data_lang'));
        $this->assertEquals($expectedTimezone, $form->getValue('timezone'));
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

    private function getUserTimezoneServiceMock(): UserTimezoneServiceInterface
    {
        $serviceMock = $this->createMock(UserTimezoneServiceInterface::class);
        $serviceMock
            ->method('getDefaultTimezone')
            ->willReturn('Europe/Minsk');
        $serviceMock
            ->method('isUserTimezoneEnabled')
            ->willReturn(true);

        return $serviceMock;
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
        $lang = new core_kernel_classes_Resource(
            new core_kernel_classes_Class(
                'http://www.tao.lu/Ontologies/TAO.rdf#Languages'
            )
        );

        $lang->setModel($this->ontologyMock);
        $lang->setLabel($name);
        $lang->setPropertyValue(
            $this->ontologyMock->getProperty(OntologyRdf::RDF_VALUE),
            $code
        );

        return $lang;
    }
}
