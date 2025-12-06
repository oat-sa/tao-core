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
 * Copyright (c) 2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\extensionManager;

use common_exception_BadRequest;
use common_ext_ExtensionsManager;
use oat\generis\test\ServiceManagerMockTrait;
use oat\tao\model\service\ApplicationService;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Renderer;
use tao_actions_ExtensionsManager;

class ExtensionManagerTest extends TestCase
{
    use ServiceManagerMockTrait;

    public function testIndexOnProductionMode(): void
    {
        $applicationServiceMock = $this->createMock(ApplicationService::class);
        $applicationServiceMock->method('isDebugMode')->willReturn(false);

        $extensionManagerMock = $this->createMock(common_ext_ExtensionsManager::class);
        $extensionManagerMock->method('getInstalledExtensions')->willReturn(['value1']);

        $serviceManagerMock = $this->getServiceManagerMock([
            ApplicationService::SERVICE_ID => $applicationServiceMock,
            common_ext_ExtensionsManager::SERVICE_ID => $extensionManagerMock,
        ]);

        $controller = new ExtensionManagerFake();
        $controller->setServiceLocator($serviceManagerMock);

        $controller->index();

        $class = new ReflectionClass(Renderer::class);
        $property = $class->getProperty('variables');
        $property->setAccessible(true);
        $variables = $property->getValue($controller->getRenderer());

        $this->assertArrayHasKey('isProduction', $variables);
        $this->assertEquals(true, $variables['isProduction']);
        $this->assertArrayHasKey('installedExtArray', $variables);
        $this->assertEquals(['value1'], $variables['installedExtArray']);
        $this->assertArrayNotHasKey('availableExtArray', $variables);
    }

    public function testIndexOnDebugMode(): void
    {
        $applicationServiceMock = $this->createMock(ApplicationService::class);
        $applicationServiceMock->method('isDebugMode')->willReturn(true);

        $extensionManagerMock = $this->createMock(common_ext_ExtensionsManager::class);
        $extensionManagerMock->method('getInstalledExtensions')->willReturn(['value1']);
        $extensionManagerMock->method('getAvailableExtensions')->willReturn(['value2']);

        $serviceManagerMock = $this->getServiceManagerMock([
            ApplicationService::SERVICE_ID => $applicationServiceMock,
            common_ext_ExtensionsManager::SERVICE_ID => $extensionManagerMock,
        ]);

        $controller = new ExtensionManagerFake();
        $controller->setServiceLocator($serviceManagerMock);

        $controller->index();

        $class = new ReflectionClass(Renderer::class);
        $property = $class->getProperty('variables');
        $property->setAccessible(true);
        $variables = $property->getValue($controller->getRenderer());

        $this->assertArrayHasKey('isProduction', $variables);
        $this->assertEquals(false, $variables['isProduction']);
        $this->assertArrayHasKey('installedExtArray', $variables);
        $this->assertEquals(['value1'], $variables['installedExtArray']);
        $this->assertArrayHasKey('availableExtArray', $variables);
        $this->assertEquals(['value2'], $variables['availableExtArray']);
    }

    protected function getExtensionManagerWithDebugMode(bool $debugMode): ExtensionManagerFake
    {
        $applicationServiceMock = $this->createMock(ApplicationService::class);
        $applicationServiceMock->method('isDebugMode')->willReturn($debugMode);

        $serviceManagerMock = $this->getServiceManagerMock([
            ApplicationService::SERVICE_ID => $applicationServiceMock,
        ]);

        $controller = new ExtensionManagerFake();
        $controller->setServiceLocator($serviceManagerMock);

        return $controller;
    }

    public function testInstallOnDebugMode(): void
    {
        $controller = $this->getExtensionManagerWithDebugMode(true);
        $this->expectException(ExpectationFailedException::class);
        $controller->install();
    }

    public function testInstallOnProductionMode(): void
    {
        $controller = $this->getExtensionManagerWithDebugMode(false);
        $this->expectException(common_exception_BadRequest::class);
        $controller->install();
    }

    public function testUnInstallOnDebugMode(): void
    {
        $controller = $this->getExtensionManagerWithDebugMode(true);
        $this->expectException(ExpectationFailedException::class);
        $controller->uninstall();
    }

    public function testUnInstallOnProductionMode(): void
    {
        $controller = $this->getExtensionManagerWithDebugMode(false);
        $this->expectException(common_exception_BadRequest::class);
        $controller->uninstall();
    }

    public function testDisableOnDebugMode(): void
    {
        $controller = $this->getExtensionManagerWithDebugMode(true);
        $this->expectException(ExpectationFailedException::class);
        $controller->disable();
    }

    public function testDisableOnProductionMode(): void
    {
        $controller = $this->getExtensionManagerWithDebugMode(false);
        $this->expectException(common_exception_BadRequest::class);
        $controller->disable();
    }

    public function testEnableOnDebugMode(): void
    {
        $controller = $this->getExtensionManagerWithDebugMode(true);
        $this->expectException(ExpectationFailedException::class);
        $controller->enable();
    }

    public function testEnableOnProductionMode(): void
    {
        $controller = $this->getExtensionManagerWithDebugMode(false);
        $this->expectException(common_exception_BadRequest::class);
        $controller->enable();
    }
}

class ExtensionManagerFake extends tao_actions_ExtensionsManager
{
    public function setView($path, $extensionID = null): void
    {
        // avoid to call Template::getTemplate
    }

    public function hasRequestParameter($name): void
    {
        // to intercept the flow and avoid to load common_request
        throw new ExpectationFailedException('HTTP request cannot be handled by unit test');
    }

    public function getRequestParameter($name): void
    {
        // to intercept the flow and avoid to load common_request
        throw new ExpectationFailedException('HTTP request cannot be handled by unit test');
    }
}
