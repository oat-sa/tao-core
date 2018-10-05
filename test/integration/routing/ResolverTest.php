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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 *
 *
 */
namespace oat\tao\test\integration\routing;

use oat\generis\test\GenerisPhpUnitTestRunner;
use oat\tao\model\routing\Resolver;
use oat\tao\test\integration\routing\samples\FooControllerA;
use common_ext_Manifest as Manifest;
use common_ext_ExtensionsManager as ExtensionsManager;
use Zend\ServiceManager\ServiceLocatorInterface;
use common_http_Request;

/**
 * Class ResolverTest
 * @package oat\tao\test\integration\routing
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class ResolverTest extends GenerisPhpUnitTestRunner {

    public function testGetExtensionId()
    {
        $serviceLocator = $this->getServiceManager();

        $fooRequest = new common_http_Request('http://tao.com/foo/FooControllerA/index');
        $resolver = new Resolver($fooRequest);
        $resolver->setServiceLocator($serviceLocator);
        $this->assertEquals('foo', $resolver->getExtensionId());

        $fooRequest = new common_http_Request('http://tao.com/legacy/LegacyController/index');
        $resolver = new Resolver($fooRequest);
        $resolver->setServiceLocator($serviceLocator);
        $this->assertEquals('legacy', $resolver->getExtensionId());
    }
    
    public function testGetControllerClass()
    {
        $serviceLocator = $this->getServiceManager();

        $fooRequest = new common_http_Request('http://tao.com/foo/FooControllerA/index');
        $resolver = new Resolver($fooRequest);
        $resolver->setServiceLocator($serviceLocator);
        $this->assertEquals(FooControllerA::class, $resolver->getControllerClass());

        $fooRequest = new common_http_Request('http://tao.com/legacy/LegacyController/index');
        $resolver = new Resolver($fooRequest);
        $resolver->setServiceLocator($serviceLocator);
        $this->assertEquals('legacy_actions_LegacyController', $resolver->getControllerClass());
    }

    public function testGetMethodName()
    {
        $serviceLocator = $this->getServiceManager();

        $fooRequest = new common_http_Request('http://tao.com/foo/FooControllerA/index');
        $resolver = new Resolver($fooRequest);
        $resolver->setServiceLocator($serviceLocator);
        $this->assertEquals('index', $resolver->getMethodName());

        $fooRequest = new common_http_Request('http://tao.com/legacy/LegacyController/index');
        $resolver = new Resolver($fooRequest);
        $resolver->setServiceLocator($serviceLocator);
        $this->assertEquals('index', $resolver->getMethodName());
    }
   
    public function testGetControllerShortName()
    {
         $serviceLocator = $this->getServiceManager();

        $fooRequest = new common_http_Request('http://tao.com/foo/FooControllerA/index');
        $resolver = new Resolver($fooRequest);
        $resolver->setServiceLocator($serviceLocator);
        $this->assertEquals('FooControllerA', $resolver->getControllerShortName());

        $fooRequest = new common_http_Request('http://tao.com/legacy/LegacyController/index');
        $resolver = new Resolver($fooRequest);
        $resolver->setServiceLocator($serviceLocator);
        $this->assertEquals('LegacyController', $resolver->getControllerShortName());
    }

    /**
     * @return ServiceLocatorInterface
     * @throws \common_Exception
     * @throws \common_ext_MalformedManifestException
     * @throws \common_ext_ManifestNotFoundException
     */
    private function getServiceManager()
    {
        $extensionsManagerMock = $this->getMockBuilder(ExtensionsManager::class)
            ->setMethods(['getInstalledExtensionsIds', 'getExtensionById'])
            ->getMock();
        $extensionsManagerMock->method('getInstalledExtensionsIds')
            ->willReturn([
                'legacy', 'foo'
            ]);

        $legacyExt = $this->getMockBuilder(\common_ext_Extension::class)
            ->setMethods(['getManifest'])
            ->setConstructorArgs(['legacy'])
            ->getMock();
        $legacyExt->method('getManifest')
            ->willReturn(new Manifest(__DIR__ . DIRECTORY_SEPARATOR . 'samples/legacyManifest.php'));

        $fooExt = $this->getMockBuilder(\common_ext_Extension::class)
            ->setMethods(['getManifest'])
            ->setConstructorArgs(['foo'])
            ->getMock();
        $fooExt->method('getManifest')
            ->willReturn(new Manifest(__DIR__ . DIRECTORY_SEPARATOR . 'samples/fooManifest.php'));

        $extensionsManagerMock->method('getExtensionById')
            ->will($this->returnValueMap([
                ['legacy', $legacyExt],
                ['foo', $fooExt],
            ]));

        $serviceLocatorMock =  $this->getServiceLocatorMock([
            ExtensionsManager::SERVICE_ID => $extensionsManagerMock
        ]);

        return $serviceLocatorMock;
    }
}
