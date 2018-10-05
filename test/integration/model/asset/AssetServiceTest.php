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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */
namespace oat\tao\test\integration\model\asset;

use oat\generis\test\GenerisPhpUnitTestRunner;
use oat\tao\model\asset\AssetService;
use oat\tao\model\service\ApplicationService;


/**
 * Test case for the Service {@link oat\tao\model\asset\AssetService}
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
class AssetServiceTest extends GenerisPhpUnitTestRunner
{
    const TEST_TAO_VERSION = 'TEST_TAO_VERSION';

    /**
     * Test the method AssetService->getAsset
     *
     * @dataProvider getAssetProvider
     */
    public function testGetAsset($baseUrl, $buster, $path, $extension, $expected)
    {
        $appServiceProphecy = $this->prophesize(ApplicationService::class);
        $appServiceProphecy->getPlatformVersion()->willReturn(self::TEST_TAO_VERSION);
        $appServiceMock = $appServiceProphecy->reveal();

        $serviceLocatorMock = $this->getServiceLocatorMock([
            ApplicationService::SERVICE_ID => $appServiceMock,
        ]);

        $options = [
            'base'   => $baseUrl,
            'buster' => $buster
        ];

        $assetService = new AssetService($options);
        $assetService->setServiceLocator($serviceLocatorMock);

        $url = $assetService->getAsset($path, $extension);

        $this->assertEquals($expected, $url, 'The asset URL matches');

    }

    /**
     * The testGetAsset data provider
     * @return array[] the test data set
     */
    public function getAssetProvider(){
        return [
            ['https://test.taotesting.com', '7654321', 'css/tao-main-style.css', 'tao', 'https://test.taotesting.com/tao/views/css/tao-main-style.css?buster=7654321'],
            ['https://test.taotesting.com/', 'AF034B', 'js/lib/require.js', 'tao', 'https://test.taotesting.com/tao/views/js/lib/require.js?buster=AF034B'],
            ['https://test.taotesting.com/', 'AF034B', 'tao/views/js/lib/require.js', null, 'https://test.taotesting.com/tao/views/js/lib/require.js?buster=AF034B'],
            ['https://test.taotesting.com/', 'éHo?/©', 'js/core/eventifier.js', 'tao', 'https://test.taotesting.com/tao/views/js/core/eventifier.js?buster=%C3%A9Ho%3F%2F%C2%A9'],
            ['https://test.taotesting.com', null, 'tao/views/js/lib/require.js', null, 'https://test.taotesting.com/tao/views/js/lib/require.js?buster='.urlencode(self::TEST_TAO_VERSION)],
            ['https://test.taotesting.com', false, 'css/tao-main-style.css', 'tao', 'https://test.taotesting.com/tao/views/css/tao-main-style.css'],
            ['https://test.taotesting.com', '7654321', 'js/path/to/library/', 'tao', 'https://test.taotesting.com/tao/views/js/path/to/library/']
        ];
    }
}
