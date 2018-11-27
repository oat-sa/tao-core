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

    public function testInstallOnDebugMode()
    {
        $controller = $this->getExtensionManagerWithDebugMode(true);
        $this->expectException(\PHPUnit_Framework_ExpectationFailedException::class);
        $controller->install();
    }

    public function testInstallOnProductionMode()
    {
        $controller = $this->getExtensionManagerWithDebugMode(false);
        $this->expectException(\common_exception_BadRequest::class);
        $controller->install();
    }

    public function testUnInstallOnDebugMode()
    {
        $controller = $this->getExtensionManagerWithDebugMode(true);
        $this->expectException(\PHPUnit_Framework_ExpectationFailedException::class);
        $controller->uninstall();
    }

    public function testUnInstallOnProductionMode()
    {
        $controller = $this->getExtensionManagerWithDebugMode(false);
        $this->expectException(\common_exception_BadRequest::class);
        $controller->uninstall();
    }

    public function testDisableOnDebugMode()
    {
        $controller = $this->getExtensionManagerWithDebugMode(true);
        $this->expectException(\PHPUnit_Framework_ExpectationFailedException::class);
        $controller->disable();
    }

    public function testDisableOnProductionMode()
    {
        $controller = $this->getExtensionManagerWithDebugMode(false);
        $this->expectException(\common_exception_BadRequest::class);
        $controller->disable();
    }


    public function testEnableOnDebugMode()
    {
        $controller = $this->getExtensionManagerWithDebugMode(true);
        $this->expectException(\PHPUnit_Framework_ExpectationFailedException::class);
        $controller->enable();
    }

    public function testEnableOnProductionMode()
    {
        $controller = $this->getExtensionManagerWithDebugMode(false);
        $this->expectException(\common_exception_BadRequest::class);
        $controller->enable();
    }
}

class ExtensionManagerFake extends \tao_actions_ExtensionsManager
{
    public function __construct()
    {
        // to avoid to set header because body is already sent
    }

    public function setView($path, $extensionID = null)
    {
        // avoid to call Template::getTemplate
    }

    public function hasRequestParameter($name)
    {
        // to intercept the flow and avoid to load common_request
        throw new \PHPUnit_Framework_ExpectationFailedException('HTTP request cannot be handled by unit test');
    }

    public function getRequestParameter($name)
    {
        // to intercept the flow and avoid to load common_request
        throw new \PHPUnit_Framework_ExpectationFailedException('HTTP request cannot be handled by unit test');
    }


}