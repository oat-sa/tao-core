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
 * Copyright (c) 2018-2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\service;

use oat\generis\test\ServiceManagerMockTrait;
use oat\oatbox\service\ServiceNotFoundException;
use oat\tao\model\service\ApplicationService;
use common_ext_Extension;
use common_ext_ExtensionsManager;
use PHPUnit\Framework\TestCase;
use common_exception_Error;
use PHPUnit\Framework\MockObject\MockObject;

class ApplicationServiceTest extends TestCase
{
    use ServiceManagerMockTrait;

    private ApplicationService $instance;

    private common_ext_Extension|MockObject $extensionMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = new ApplicationService();
        $this->extensionMock = $this->createMock(common_ext_Extension::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->instance, $this->extensionMock);
    }

    private function mockServiceManager(): void
    {
        $extensionManagerMock = $this->createMock(common_ext_ExtensionsManager::class);
        $extensionManagerMock
            ->method('getExtensionById')
            ->with('tao')
            ->willReturn($this->extensionMock);

        $serviceManagerMock = $this->getServiceManagerMock([
            common_ext_ExtensionsManager::SERVICE_ID => $extensionManagerMock,
        ]);
        $this->instance->setServiceManager($serviceManagerMock);
    }

    public function testGetConstantValueServiceLocatorEmpty(): void
    {
        $this->expectException(common_exception_Error::class);

        $this->instance->getProductName();
    }

    public function testGetConstantValueExtensionManagerNotRegistered(): void
    {
        $this->expectException(ServiceNotFoundException::class);

        $serviceManagerMock = $this->getServiceManagerMock();
        $serviceManagerMock
            ->method('get')
            ->with(common_ext_ExtensionsManager::SERVICE_ID)
            ->willThrowException(new ServiceNotFoundException(common_ext_ExtensionsManager::SERVICE_ID));

        $this->instance->setServiceManager($serviceManagerMock);

        $this->instance->getProductName();
    }

    public function testGetConstantValueNotExistingConstant(): void
    {
        $this->expectException(common_exception_Error::class);

        $this->extensionMock
            ->expects($this->once())
            ->method('getConstant')
            ->willThrowException(new common_exception_Error());
        $this->mockServiceManager();

        $this->instance->getProductName();
    }

    public function testGetPlatformVersion(): void
    {
        $expectedVersion = 'TEST_TAO_VERSION';
        $this->extensionMock
            ->expects($this->once())
            ->method('getConstant')
            ->with('TAO_VERSION')
            ->willReturn($expectedVersion);
        $this->mockServiceManager();

        $version = $this->instance->getPlatformVersion();

        $this->assertEquals($expectedVersion, $version, 'Version must be as expected.');
    }

    /**
     * @dataProvider isDemoProvider
     */
    public function testIsDemo(string $releaseStatus, bool $expectedResult): void
    {
        $this->extensionMock
            ->expects($this->once())
            ->method('getConstant')
            ->with('TAO_RELEASE_STATUS')
            ->willReturn($releaseStatus);
        $this->mockServiceManager();

        $releaseStatus = $this->instance->isDemo();

        $this->assertEquals($expectedResult, $releaseStatus, 'Release statusmust be as expected.');
    }

    public function testGetDefaultEncoding(): void
    {
        $expectedEncoding = 'TEST_ENCODING';

        $this->extensionMock
            ->expects($this->once())
            ->method('getConstant')
            ->with('TAO_DEFAULT_ENCODING')
            ->willReturn($expectedEncoding);
        $this->mockServiceManager();

        $encoding = $this->instance->getDefaultEncoding();

        $this->assertEquals($expectedEncoding, $encoding, 'Product name must be as expected.');
    }

    public function testGetProductName(): void
    {
        $expectedProductName = 'TEST_PROSUCT_NAME';

        $this->extensionMock
            ->expects($this->once())
            ->method('getConstant')
            ->with('PRODUCT_NAME')
            ->willReturn($expectedProductName);
        $this->mockServiceManager();

        $productName = $this->instance->getProductName();

        $this->assertEquals($expectedProductName, $productName, 'Product name must be as expected.');
    }

    /**
     * @dataProvider providerGetVersionName
     */
    public function testGetVersionName(?string $buildNumber, string $taoVersion, string $expected): void
    {
        $this->extensionMock
            ->expects($this->once())
            ->method('getConstant')
            ->with('TAO_VERSION')
            ->willReturn($taoVersion);
        $this->mockServiceManager();

        if (!is_null($buildNumber)) {
            $this->instance->setOption(ApplicationService::OPTION_BUILD_NUMBER, $buildNumber);
        }

        $versionName = $this->instance->getVersionName();

        $this->assertEquals($expected, $versionName, 'Version name must be as expected.');
    }

    public function isDemoProvider(): array
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

    public function providerGetVersionName(): array
    {
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
