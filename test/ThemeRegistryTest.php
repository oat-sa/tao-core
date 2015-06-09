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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */
namespace oat\tao\test;

use oat\tao\test\TaoPhpUnitTestRunner;
use oat\tao\model\ThemeRegistry;

/**
 *
 * @author Lionel Lecaque, lionel@taotesting.com
 */
class ThemeRegistryTest extends TaoPhpUnitTestRunner
{

    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function setUp()
    {
        TaoPhpUnitTestRunner::initTest();
    }


    public function testRegister()
    {
        ThemeRegistry::getRegistry()->register('Black on Light Magenta',array('items','tests'));

        $map = ThemeRegistry::getRegistry()->getMap();
        $this->assertFalse(empty($map));
        $this->assertInternalType('array', $map);
        $this->assertArrayHasKey('blackOnLightMagenta' , $map);
        $this->assertInternalType('array', $map['blackOnLightMagenta']);
        $this->assertArrayHasKey('name', $map['blackOnLightMagenta']);
        $this->assertEquals('Black on Light Magenta', $map['blackOnLightMagenta']['name']);
        $this->assertArrayHasKey('targets', $map['blackOnLightMagenta']);
        $this->assertEquals(array('items','tests'), $map['blackOnLightMagenta']['targets']);


        ThemeRegistry::getRegistry()->remove('blackOnLightMagenta');
    }

    public function testGetAvaillableTheme()
    {
        ThemeRegistry::getRegistry()->register('Black on Light Magenta',array('items','tests'));
        ThemeRegistry::getRegistry()->register('Light Blue on Dark Blue',array('items'));
        ThemeRegistry::getRegistry()->register('Black on White');

        $map = ThemeRegistry::getRegistry()->getAvailableThemes();
        var_dump($map);

//         ThemeRegistry::getRegistry()->remove('blackOnLightMagenta');
//         ThemeRegistry::getRegistry()->remove('lightBlueOnDarkBlue');
//         ThemeRegistry::getRegistry()->remove('blackOnWhite');


    }
}

?>