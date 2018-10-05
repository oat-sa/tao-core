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
 */

namespace oat\tao\test\unit\service;

use oat\generis\test\GenerisPhpUnitTestRunner;
use oat\oatbox\service\ServiceNotFoundException;
use oat\tao\model\service\ApplicationService;
use common_ext_Extension;
use common_ext_ExtensionsManager;
use Zend\ServiceManager\ServiceLocatorInterface;
use common_exception_Error;

/**
 * Class ApplicationServiceTest
 * @package oat\tao\test\unit\service
 */
class ApplicationServiceTest extends GenerisPhpUnitTestRunner
{
    /**
     * @var ApplicationService
     */
    private $instance;

    /**
     * @var common_ext_Extension|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionMock;

    protected function setUp()
    {
        parent::setUp();

        $this->instance = new ApplicationService();
        $this->extensionMock = $this->getMock(common_ext_Extension::class, [], [], '', false);
    }

    protected function tearDown()
    {
        parent::tearDown();
        unset($this->instance, $this->extensionMock);
    }

    private function mockServiceLocator()
    {
        $extensionManagerProphecy = $this->prophesize(common_ext_ExtensionsManager::class);
        $extensionManagerProphecy->getExtensionById('tao')->willReturn($this->extensionMock);
        $extensionManagerMock = $extensionManagerProphecy->reveal();

        $serviceLocatorMock = $this->getServiceLocatorMock([
            common_ext_ExtensionsManager::SERVICE_ID => $extensionManagerMock,
        ]);
        $this->instance->setServiceLocator($serviceLocatorMock);
    }

    public function testGetConstantValueServiceLocatorEmpty()
    {
        $this->expectException(common_exception_Error::class);

        $this->instance->getProductName();
    }

    public function testGetConstantValueExtensionManagerNotRegistered()
    {
        $this->expectException(ServiceNotFoundException::class);

        $serviceLocatorProphecy = $this->prophesize(ServiceLocatorInterface::class);
        $serviceLocatorProphecy->get(common_ext_ExtensionsManager::SERVICE_ID)->willThrow(ServiceNotFoundException::class);
        $serviceLocatorMock = $serviceLocatorProphecy->reveal();

        $this->instance->setServiceLocator($serviceLocatorMock);

        $this->instance->getProductName();
    }

    public function testGetConstantValueNotExistingConstant()
    {
        $this->expectException(common_exception_Error::class);

        $this->extensionMock->expects($this->once())
            ->method('getConstant')
            ->willThrowException(new common_exception_Error());
        $this->mockServiceLocator();

        $this->instance->getProductName();
    }

    public function testGetPlatformVersion()
    {
        $expectedVersion = 'TEST_TAO_VERSION';
        $this->extensionMock->expects($this->once())
            ->method('getConstant')
            ->with('TAO_VERSION')
            ->willReturn($expectedVersion);
        $this->mockServiceLocator();

        $version = $this->instance->getPlatformVersion();

        $this->assertEquals($expectedVersion, $version, 'Version must be as expected.');
    }

    /**
     * @dataProvider isDemoProvider
     */
    public function testIsDemo($releaseStatus, $expectedResult)
    {
        $this->extensionMock->expects($this->once())
            ->method('getConstant')
            ->with('TAO_RELEASE_STATUS')
            ->willReturn($releaseStatus);
        $this->mockServiceLocator();

        $releaseStatus = $this->instance->isDemo();

        $this->assertEquals($expectedResult, $releaseStatus, 'Release statusmust be as expected.');
    }

    public function testGetDefaultEncoding()
    {
        $expectedEncoding = 'TEST_ENCODING';

        $this->extensionMock->expects($this->once())
            ->method('getConstant')
            ->with('TAO_DEFAULT_ENCODING')
            ->willReturn($expectedEncoding);
        $this->mockServiceLocator();

        $encoding = $this->instance->getDefaultEncoding();

        $this->assertEquals($expectedEncoding, $encoding, 'Product name must be as expected.');
    }

    public function testGetProductName()
    {
        $expectedProductName = 'TEST_PROSUCT_NAME';

        $this->extensionMock->expects($this->once())
            ->method('getConstant')
            ->with('PRODUCT_NAME')
            ->willReturn($expectedProductName);
        $this->mockServiceLocator();

        $productName = $this->instance->getProductName();

        $this->assertEquals($expectedProductName, $productName, 'Product name must be as expected.');
    }

    /**
     * Test for getVersionName method
     *
     * @dataProvider providerGetVersionName
     *
     * @param $buildNumber
     * @param $taoVersion
     * @param $expected
     * @throws \common_ext_ExtensionException
     * @throws common_exception_Error
     */
    public function testGetVersionName($buildNumber, $taoVersion, $expected) {
        $this->extensionMock->expects($this->once())
            ->method('getConstant')
            ->with('TAO_VERSION')
            ->willReturn($taoVersion);
        $this->mockServiceLocator();

        if (!is_null($buildNumber)) {
            $this->instance->setOption(ApplicationService::OPTION_BUILD_NUMBER, $buildNumber);
        }

        $versionName = $this->instance->getVersionName();

        $this->assertEquals($expected, $versionName, 'Version name must be as expected.');
    }

    public function isDemoProvider()
    {
        return [
            [
                'releaseStatus' => 'stable',
                'expectedResult' => false,
            ],
            [
                'releaseStatus' => 'demo',
                'expectedResult' => true,
            ],
            [
                'releaseStatus' => 'demoA',
                'expectedResult' => true,
            ],
            [
                'releaseStatus' => 'demoB',
                'expectedResult' => true,
            ],
            [
                'releaseStatus' => 'demoS',
                'expectedResult' => true,
            ],
        ];
    }

    /**
     * Data provider for testGetVersionName
     *
     * @return array
     */
    public function providerGetVersionName() {
        return [
            'Without build number' => [
                'buildNumber' => null,
                'taoVersion' => 'TAO_VERSION',
                'expected' => 'TAO_VERSION'
            ],
            'With empty number' => [
                'buildNumber' => '',
                'taoVersion' => 'TAO_VERSION',
                'expected' => 'vTAO_VERSION'
            ],
            'With not numeric number' => [
                'buildNumber' => 'NOT_NUMERIC_NUMBER',
                'taoVersion' => 'TAO_VERSION',
                'expected' => 'vTAO_VERSION'
            ],
            'With numeric number' => [
                'buildNumber' => '123',
                'taoVersion' => 'TAO_VERSION',
                'expected' => 'vTAO_VERSION+build123'
            ],
        ];
    }
}
