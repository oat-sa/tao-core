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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
 *
 */
namespace oat\tao\test\integration\model\layout;

use oat\tao\test\TaoPhpUnitTestRunner;
use oat\tao\model\layout\AmdLoader;


/**
 * Test case for oat\tao\model\layout\AmdLoader
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
class LayoutTest extends TaoPhpUnitTestRunner
{
    /**
     * Test building the AMD script loader
     *
     * @dataProvider amdLoaderProvider
     */
    public function testAmdLoader($configUrl, $requireJsUrl, $bootstrapUrl, $bundle, $controller, $params, $result){

        $loader = new AmdLoader($configUrl, $requireJsUrl, $bootstrapUrl);
        if($bundle){
            $this->assertEquals($loader->getBundleLoader($bundle, $controller, $params), $result);
        } else {
            $this->assertEquals($loader->getDynamicLoader($controller, $params), $result);
        }
    }

    /**
     * Provides data for the testAmdLoader case
     */
    public function amdLoaderProvider(){
        return [
            ['tao/Config/config', 'lib/require.js', 'loader/bootstrap', 'loader/main.min.js', 'controller/main', null,
             '<script id="amd-loader" data-config="tao/Config/config" src="loader/main.min.js" data-controller="controller/main"></script>'],
            ['tao/Config/config', 'lib/require.js', 'loader/bootstrap', false, 'controller/main', null,
             '<script id="amd-loader" data-config="tao/Config/config" src="lib/require.js" data-main="loader/bootstrap" data-controller="controller/main"></script>'],
            ['tao/Config/config', 'lib/require.js', 'loader/bootstrap', 'loader/login.min.js', 'controller/login', [ 'foo' => 'bar'],
             '<script id="amd-loader" data-config="tao/Config/config" src="loader/login.min.js" data-controller="controller/login" data-params="{&quot;foo&quot;:&quot;bar&quot;}"></script>'],
            ['tao/Config/config', 'lib/require.js', 'loader/bootstrap', false, 'controller/login', [ 'foo' => 'bar'],
             '<script id="amd-loader" data-config="tao/Config/config" src="lib/require.js" data-main="loader/bootstrap" data-controller="controller/login" data-params="{&quot;foo&quot;:&quot;bar&quot;}"></script>'],
        ];
    }
}
