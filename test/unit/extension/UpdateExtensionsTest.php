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
 * Copyright (c) 2020-2025 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

declare(strict_types=1);

namespace oat\tao\test\unit\extension;

use oat\generis\model\data\Ontology;
use oat\generis\model\data\RdfsInterface;
use oat\generis\persistence\PersistenceManager;
use PHPUnit\Framework\MockObject\MockObject;
use oat\generis\test\PersistenceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\oatbox\event\EventManager;
use oat\oatbox\log\LoggerService;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\asset\AssetService;
use oat\tao\model\extension\UpdateExtensions;
use common_report_Report as Report;
use common_ext_ExtensionsManager as ExtensionsManager;
use oat\tao\model\migrations\MigrationsService;
use common_ext_Extension as Extension;
use common_ext_Manifest as Manifest;
use core_kernel_persistence_ClassInterface as ClassImplementation;

/**
 * Extends the generis updater to take into account
 * the translation files
 */
class UpdateExtensionsTest extends TestCase
{
    use PersistenceManagerMockTrait;

    protected function setUp(): void
    {
        defined('TAO_VERSION') ?: define('TAO_VERSION', 'TAO_VERSION');
    }

    public function testInvoke()
    {

        $config = new \common_persistence_KeyValuePersistence([], new \common_persistence_InMemoryKvDriver());
        ServiceManager::setServiceManager(new ServiceManager($config));
        $config->set(ExtensionsManager::SERVICE_ID, $this->getExtensionManagerMock());
        $config->set(MigrationsService::SERVICE_ID, $this->getMigrationsServiceMock());
        $config->set(PersistenceManager::SERVICE_ID, $this->getPersistenceManagerMock('unittest'));
        $config->set(Ontology::SERVICE_ID, $this->getOntologyMock());
        $config->set(AssetService::SERVICE_ID, new AssetService());
        $config->set('generis/log', new LoggerService([]));
        $config->set('generis/event', new EventManager());

        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', '');
        }

        $updater = new UpdateExtensions();

        $updater->setServiceLocator(ServiceManager::getServiceManager());
        $report = $updater([]);

        $this->assertInstanceOf(Report::class, $report);
        $reports = $report->getChildren();
        $this->assertCount(6, $reports);
        $this->assertEquals('foo already up to date', $reports[0]->getMessage());
        $this->assertEquals('bar already up to date', $reports[1]->getMessage());
        $this->assertEquals('Migrations applied', $reports[2]->getMessage());
    }

    private function getOntologyMock()
    {
        $classImplementation = $this->createMock(ClassImplementation::class);
        /** @var RdfsInterface|MockObject $rdfsInterface */
        $rdfsInterface = $this->createConfiguredMock(
            RdfsInterface::class,
            ['getClassImplementation' => $classImplementation]
        );

        $ontologyMock = $this->createMock(Ontology::class);
        $ontologyMock->method('getResource')
            ->willReturn(new \core_kernel_classes_Resource('foo'));
        $ontologyMock->method('getRdfsInterface')
            ->willReturn($rdfsInterface);
        return $ontologyMock;
    }


    private function getMigrationsServiceMock()
    {
        $migrationsService = $this->getMockBuilder(MigrationsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $migrationsService->method('migrate')
            ->willReturn(new Report(Report::TYPE_SUCCESS, 'Migrations applied'));

        return $migrationsService;
    }

    private function getExtensionManagerMock()
    {
        $fooManifest = $this->getMockBuilder(Manifest::class)->disableOriginalConstructor()
            ->getMock();
        $fooManifest->method('getDependencies')
            ->willReturn([]);

        $extensionFoo = $this->getMockBuilder(Extension::class)
            ->disableOriginalConstructor()
            ->getMock();
        $extensionFoo->method('getId')
            ->willReturn('foo');
        $extensionFoo->method('getName')
            ->willReturn('foo');
        $extensionFoo->method('getDependencies')
            ->willReturn([]);
        $extensionFoo->method('getManifest')
            ->willReturn($fooManifest);
        $extensionFoo->method('getDir')
            ->willReturn(dirname(dirname(dirname(__DIR__))));

        $barManifest = $this->getMockBuilder(Manifest::class)->disableOriginalConstructor()
            ->getMock();
        $barManifest->method('getDependencies')
            ->willReturn([]);
        $extensionBar = $this->getMockBuilder(Extension::class)
            ->disableOriginalConstructor()
            ->getMock();
        $extensionBar->method('getId')
            ->willReturn('bar');
        $extensionBar->method('getName')
            ->willReturn('bar');
        $extensionBar->method('getDependencies')
            ->willReturn(['foo' => '*']);
        $extensionBar->method('getManifest')
            ->willReturn($barManifest);
        $extensionBar->method('getDir')
            ->willReturn(dirname(dirname(dirname(__DIR__))));


        $extensionsManagerMock = $this->getMockBuilder(ExtensionsManager::class)
            ->getMock();
        $extensionsManagerMock->method('isInstalled')->willReturn(true);
        $extensionsManagerMock->method('getInstalledExtensions')
            ->willReturn([
                'bar' => $extensionBar,
                'foo' => $extensionFoo,
            ]);
        $extensionsManagerMock->method('getExtensionById')
            ->willReturnMap([
                ['bar', $extensionBar],
                ['foo', $extensionFoo],
            ]);
        return $extensionsManagerMock;
    }
}
