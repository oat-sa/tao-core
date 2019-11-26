<?php

declare(strict_types=1);

namespace oat\tao\test\unit\extensionManager;

use oat\generis\test\TestCase;
use oat\tao\model\service\ApplicationService;

class ExtensionManagerTest extends TestCase
{
    public function testIndexOnProductionMode(): void
    {
        $applicationServiceMock = $this->prophesize(ApplicationService::class);
        $applicationServiceMock->isDebugMode()->willReturn(false);

        $extensionManagerMock = $this->prophesize(\common_ext_ExtensionsManager::class);
        $extensionManagerMock->getInstalledExtensions()->willReturn(['value1']);

        $serviceLocatorMock = $this->getServiceLocatorMock([
            ApplicationService::SERVICE_ID => $applicationServiceMock->reveal(),
            \common_ext_ExtensionsManager::SERVICE_ID => $extensionManagerMock->reveal(),
        ]);

        $controller = new ExtensionManagerFake();
        $controller->setServiceLocator($serviceLocatorMock);

        $controller->index();

        $class = new \ReflectionClass(\Renderer::class);
        $property = $class->getProperty('variables');
        $property->setAccessible(true);
        $variables = $property->getValue($controller->getRenderer());

        $this->assertArrayHasKey('isProduction', $variables);
        $this->assertSame(true, $variables['isProduction']);
        $this->assertArrayHasKey('installedExtArray', $variables);
        $this->assertSame(['value1'], $variables['installedExtArray']);
        $this->assertArrayNotHasKey('availableExtArray', $variables);
    }

    public function testIndexOnDebugMode(): void
    {
        $applicationServiceMock = $this->prophesize(ApplicationService::class);
        $applicationServiceMock->isDebugMode()->willReturn(true);

        $extensionManagerMock = $this->prophesize(\common_ext_ExtensionsManager::class);
        $extensionManagerMock->getInstalledExtensions()->willReturn(['value1']);
        $extensionManagerMock->getAvailableExtensions()->willReturn(['value2']);

        $serviceLocatorMock = $this->getServiceLocatorMock([
            ApplicationService::SERVICE_ID => $applicationServiceMock->reveal(),
            \common_ext_ExtensionsManager::SERVICE_ID => $extensionManagerMock->reveal(),
        ]);

        $controller = new ExtensionManagerFake();
        $controller->setServiceLocator($serviceLocatorMock);

        $controller->index();

        $class = new \ReflectionClass(\Renderer::class);
        $property = $class->getProperty('variables');
        $property->setAccessible(true);
        $variables = $property->getValue($controller->getRenderer());

        $this->assertArrayHasKey('isProduction', $variables);
        $this->assertSame(false, $variables['isProduction']);
        $this->assertArrayHasKey('installedExtArray', $variables);
        $this->assertSame(['value1'], $variables['installedExtArray']);
        $this->assertArrayHasKey('availableExtArray', $variables);
        $this->assertSame(['value2'], $variables['availableExtArray']);
    }

    public function testInstallOnDebugMode(): void
    {
        $controller = $this->getExtensionManagerWithDebugMode(true);
        $this->expectException(\PHPUnit_Framework_ExpectationFailedException::class);
        $controller->install();
    }

    public function testInstallOnProductionMode(): void
    {
        $controller = $this->getExtensionManagerWithDebugMode(false);
        $this->expectException(\common_exception_BadRequest::class);
        $controller->install();
    }

    public function testUnInstallOnDebugMode(): void
    {
        $controller = $this->getExtensionManagerWithDebugMode(true);
        $this->expectException(\PHPUnit_Framework_ExpectationFailedException::class);
        $controller->uninstall();
    }

    public function testUnInstallOnProductionMode(): void
    {
        $controller = $this->getExtensionManagerWithDebugMode(false);
        $this->expectException(\common_exception_BadRequest::class);
        $controller->uninstall();
    }

    public function testDisableOnDebugMode(): void
    {
        $controller = $this->getExtensionManagerWithDebugMode(true);
        $this->expectException(\PHPUnit_Framework_ExpectationFailedException::class);
        $controller->disable();
    }

    public function testDisableOnProductionMode(): void
    {
        $controller = $this->getExtensionManagerWithDebugMode(false);
        $this->expectException(\common_exception_BadRequest::class);
        $controller->disable();
    }

    public function testEnableOnDebugMode(): void
    {
        $controller = $this->getExtensionManagerWithDebugMode(true);
        $this->expectException(\PHPUnit_Framework_ExpectationFailedException::class);
        $controller->enable();
    }

    public function testEnableOnProductionMode(): void
    {
        $controller = $this->getExtensionManagerWithDebugMode(false);
        $this->expectException(\common_exception_BadRequest::class);
        $controller->enable();
    }

    /**
     * @param $debugMode
     * @return ExtensionManagerFake
     */
    protected function getExtensionManagerWithDebugMode($debugMode)
    {
        $applicationServiceMock = $this->prophesize(ApplicationService::class);
        $applicationServiceMock->isDebugMode()->willReturn($debugMode);

        $serviceLocatorMock = $this->getServiceLocatorMock([
            ApplicationService::SERVICE_ID => $applicationServiceMock->reveal(),
        ]);

        $controller = new ExtensionManagerFake();
        $controller->setServiceLocator($serviceLocatorMock);

        return $controller;
    }
}

class ExtensionManagerFake extends \tao_actions_ExtensionsManager
{
    public function __construct()
    {
        // to avoid to set header because body is already sent
    }

    public function setView($path, $extensionID = null): void
    {
        // avoid to call Template::getTemplate
    }

    public function hasRequestParameter($name): void
    {
        // to intercept the flow and avoid to load common_request
        throw new \PHPUnit_Framework_ExpectationFailedException('HTTP request cannot be handled by unit test');
    }

    public function getRequestParameter($name): void
    {
        // to intercept the flow and avoid to load common_request
        throw new \PHPUnit_Framework_ExpectationFailedException('HTTP request cannot be handled by unit test');
    }
}
