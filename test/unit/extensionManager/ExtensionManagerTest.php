<?php

namespace oat\tao\test\unit\extensionManager;

use oat\generis\test\TestCase;
use oat\tao\model\service\ApplicationService;

class ExtensionManagerTest extends TestCase
{
    public function testIndexOnProductionMode()
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
        $this->assertEquals(true, $variables['isProduction']);
        $this->assertArrayHasKey('installedExtArray', $variables);
        $this->assertEquals(['value1'], $variables['installedExtArray']);
        $this->assertArrayNotHasKey('availableExtArray', $variables);
    }

    public function testIndexOnDebugMode()
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
        $this->assertEquals(false, $variables['isProduction']);
        $this->assertArrayHasKey('installedExtArray', $variables);
        $this->assertEquals(['value1'], $variables['installedExtArray']);
        $this->assertArrayHasKey('availableExtArray', $variables);
        $this->assertEquals(['value2'], $variables['availableExtArray']);
    }

    public function testInstallOnDebugMode()
    {
        $applicationServiceMock = $this->prophesize(ApplicationService::class);
        $applicationServiceMock->isDebugMode()->willReturn(true);

        $serviceLocatorMock = $this->getServiceLocatorMock([
            ApplicationService::SERVICE_ID => $applicationServiceMock->reveal(),
        ]);

        $this->expectException(\PHPUnit_Framework_ExpectationFailedException::class);

        $controller = new ExtensionManagerFake();
        $controller->setServiceLocator($serviceLocatorMock);

        $controller->install();
    }

    public function testInstallOnProductionMode()
    {
        $applicationServiceMock = $this->prophesize(ApplicationService::class);
        $applicationServiceMock->isDebugMode()->willReturn(false);

        $serviceLocatorMock = $this->getServiceLocatorMock([
            ApplicationService::SERVICE_ID => $applicationServiceMock->reveal(),
        ]);

        $this->expectException(\common_exception_BadRequest::class);

        $controller = new ExtensionManagerFake();
        $controller->setServiceLocator($serviceLocatorMock);

        $controller->install();
    }

    public function testUnInstallOnProductionMode()
    {
        $applicationServiceMock = $this->prophesize(ApplicationService::class);
        $applicationServiceMock->isDebugMode()->willReturn(false);

        $serviceLocatorMock = $this->getServiceLocatorMock([
            ApplicationService::SERVICE_ID => $applicationServiceMock->reveal(),
        ]);

        $this->expectException(\common_exception_BadRequest::class);

        $controller = new ExtensionManagerFake();
        $controller->setServiceLocator($serviceLocatorMock);

        $controller->uninstall();
    }

    public function testUnInstallOnDebugMode()
    {
        $applicationServiceMock = $this->prophesize(ApplicationService::class);
        $applicationServiceMock->isDebugMode()->willReturn(true);

        $serviceLocatorMock = $this->getServiceLocatorMock([
            ApplicationService::SERVICE_ID => $applicationServiceMock->reveal(),
        ]);

        $this->expectException(\PHPUnit_Framework_ExpectationFailedException::class);

        $controller = new ExtensionManagerFake();
        $controller->setServiceLocator($serviceLocatorMock);

        $controller->uninstall();
    }

    public function testDisableOnProductionMode()
    {
        $applicationServiceMock = $this->prophesize(ApplicationService::class);
        $applicationServiceMock->isDebugMode()->willReturn(false);

        $serviceLocatorMock = $this->getServiceLocatorMock([
            ApplicationService::SERVICE_ID => $applicationServiceMock->reveal(),
        ]);

        $this->expectException(\common_exception_BadRequest::class);

        $controller = new ExtensionManagerFake();
        $controller->setServiceLocator($serviceLocatorMock);

        $controller->disable();
    }

    public function testDisableOnDebugMode()
    {
        $applicationServiceMock = $this->prophesize(ApplicationService::class);
        $applicationServiceMock->isDebugMode()->willReturn(true);

        $serviceLocatorMock = $this->getServiceLocatorMock([
            ApplicationService::SERVICE_ID => $applicationServiceMock->reveal(),
        ]);

        $this->expectException(\PHPUnit_Framework_ExpectationFailedException::class);

        $controller = new ExtensionManagerFake();
        $controller->setServiceLocator($serviceLocatorMock);

        $controller->disable();
    }

    public function testEnableOnDebugMode()
    {
        $applicationServiceMock = $this->prophesize(ApplicationService::class);
        $applicationServiceMock->isDebugMode()->willReturn(true);

        $serviceLocatorMock = $this->getServiceLocatorMock([
            ApplicationService::SERVICE_ID => $applicationServiceMock->reveal(),
        ]);

        $this->expectException(\PHPUnit_Framework_ExpectationFailedException::class);

        $controller = new ExtensionManagerFake();
        $controller->setServiceLocator($serviceLocatorMock);

        $controller->enable();
    }

    public function testEnableOnProductionMode()
    {
        $applicationServiceMock = $this->prophesize(ApplicationService::class);
        $applicationServiceMock->isDebugMode()->willReturn(false);

        $serviceLocatorMock = $this->getServiceLocatorMock([
            ApplicationService::SERVICE_ID => $applicationServiceMock->reveal(),
        ]);

        $this->expectException(\common_exception_BadRequest::class);

        $controller = new ExtensionManagerFake();
        $controller->setServiceLocator($serviceLocatorMock);

        $controller->enable();
    }
}

class ExtensionManagerFake extends \tao_actions_ExtensionsManager
{
    public function __construct()
    {
    }

    public function setView($path, $extensionID = null)
    {
    }

    public function hasRequestParameter($name)
    {
        throw new \PHPUnit_Framework_ExpectationFailedException('HTTP request cannot be handled by unit test');
    }

    public function getRequestParameter($name)
    {
        throw new \PHPUnit_Framework_ExpectationFailedException('HTTP request cannot be handled by unit test');
    }


}