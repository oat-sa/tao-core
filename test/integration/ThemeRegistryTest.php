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

namespace oat\tao\test\integration;

use common_Exception;
use oat\tao\model\ThemeRegistry;
use oat\generis\test\GenerisPhpUnitTestRunner;
use oat\tao\model\websource\WebsourceManager;

/**
 *
 * @author Lionel Lecaque, lionel@taotesting.com
 */
class ThemeRegistryTest extends GenerisPhpUnitTestRunner
{

    public function tearDown(): void
    {
        parent::tearDown();

        ThemeRegistry::getRegistry()->remove('itemsTest');
        ThemeRegistry::getRegistry()->remove('testsTest');
    }

    public function testsetDefaultTheme()
    {
        ThemeRegistry::getRegistry()->createTarget('itemsTest', 'base');
        ThemeRegistry::getRegistry()->registerTheme('lightBlueOnDarkBlue', 'Light Blue on Dark Bluea', 'path', ['itemsTest']);
        ThemeRegistry::getRegistry()->setDefaultTheme('itemsTest', 'lightBlueOnDarkBlue');

        $map = ThemeRegistry::getRegistry()->getMap();
        $this->assertFalse(empty($map));
        $this->assertisarray($map);

        $this->assertArrayHasKey('itemsTest', $map);

        $this->assertisarray($map['itemsTest']);
        $this->assertArrayHasKey('available', $map['itemsTest']);

        $available = current($map['itemsTest']['available']);
        $this->assertisarray($available);
        $this->assertArrayHasKey('name', $available);
        $this->assertEquals('Light Blue on Dark Bluea', $available['name']);

        $defaultTheme = ThemeRegistry::getRegistry()->getDefaultTheme('itemsTest');
        $this->assertEquals('lightBlueOnDarkBlue', $defaultTheme['id']);

        // target exist
        ThemeRegistry::getRegistry()->remove('itemsTest');
    }

    public function testRegister()
    {
        ThemeRegistry::getRegistry()->createTarget('itemsTest', 'base');
        ThemeRegistry::getRegistry()->registerTheme('blackOnLightMagenta', 'Black on Light Magenta', 'blackOnLightMagenta', ['itemsTest']);

        $map = ThemeRegistry::getRegistry()->getMap();
        $this->assertFalse(empty($map));
        $this->assertisarray($map);

        $this->assertArrayHasKey('itemsTest', $map);
        $this->assertisarray($map['itemsTest']);

        $this->assertisarray($map['itemsTest']);
        $this->assertArrayHasKey('available', $map['itemsTest']);

        $available = current($map['itemsTest']['available']);
        $this->assertisarray($available);
        $this->assertArrayHasKey('name', $available);

        $this->assertEquals('Black on Light Magenta', $available['name']);

        ThemeRegistry::getRegistry()->createTarget('testsTest', 'base');
        ThemeRegistry::getRegistry()->registerTheme('lightBlueOnDarkBlue', 'Light Blue on Dark Blue', 'lightBlueOnDarkBlue', ['itemsTest', 'testsTest']);

        $map = ThemeRegistry::getRegistry()->getMap();

        $this->assertArrayHasKey('testsTest', $map);
        $this->assertisarray($map['testsTest']);

        $this->assertArrayHasKey('available', $map['testsTest']);

        $available = current($map['testsTest']['available']);
        $this->assertisarray($available);
        $this->assertArrayHasKey('name', $available);

        $this->assertEquals('Light Blue on Dark Blue', $available['name']);

        foreach ($map['itemsTest']['available'] as $theme) {
            $this->assertisarray($theme);
            $this->assertArrayHasKey('id', $theme);
            $this->assertTrue(in_array($theme['id'], [
                'blackOnLightMagenta',
                'lightBlueOnDarkBlue'
            ]));
        }

        ThemeRegistry::getRegistry()->registerTheme('superAccess', 'super accessibility theme', '', ['itemsTest'], ['tplA' => 'taoAccess/theme/A.tpl']);
        $map = ThemeRegistry::getRegistry()->getMap();
        $this->assertEquals(3, count($map['itemsTest']['available']));
        $superAccessTheme = $map['itemsTest']['available'][2];

        $this->assertEquals('superAccess', $superAccessTheme['id']);
        $this->assertEquals(1, count($superAccessTheme['templates']));
        $this->assertEquals('taoAccess/theme/A.tpl', $superAccessTheme['templates']['tplA']);
    }

    public function testGetTemplate()
    {

        ThemeRegistry::getRegistry()->createTarget('itemsTest', 'base');
        ThemeRegistry::getRegistry()->registerTheme('superAccess', 'super accessibility theme', '', ['itemsTest'], ['tplA' => 'taoAccess/theme/A.tpl']);
        ThemeRegistry::getRegistry()->registerTheme('superAccessNoTpl', 'super accessibility theme without tpl', '', ['itemsTest']);
        $this->assertNotEmpty(ThemeRegistry::getRegistry()->getTemplate('itemsTest', 'superAccess', 'tplA'));
        $this->assertEmpty(ThemeRegistry::getRegistry()->getTemplate('itemsTest', 'superAccess', 'tplB'));
        $this->assertEmpty(ThemeRegistry::getRegistry()->getTemplate('itemsTest', 'superAccessNoTpl', 'tplA'));
    }

    public function testUnregisterTheme()
    {
        ThemeRegistry::getRegistry()->createTarget('itemsTest', 'base');
        ThemeRegistry::getRegistry()->createTarget('testsTest', 'base');
        ThemeRegistry::getRegistry()->registerTheme('blackOnLightMagenta', 'Black on Light Magenta', 'blackOnLightMagenta', ['itemsTest']);
        ThemeRegistry::getRegistry()->registerTheme('lightBlueOnDarkBlue', 'Light Blue on Dark Blue', 'lightBlueOnDarkBlue', ['itemsTest', 'testsTest']);

        ThemeRegistry::getRegistry()->unregisterTheme('blackOnLightMagenta');

        $map = ThemeRegistry::getRegistry()->getMap();

        $this->assertArrayHasKey('itemsTest', $map);
        $this->assertEquals(1, count($map['itemsTest']['available'])); //only one theme left
        $theme = current($map['itemsTest']['available']);
        $this->assertEquals($theme['id'], 'lightBlueOnDarkBlue');//and this theme is not the deleted one

        ThemeRegistry::getRegistry()->unregisterTheme('lightBlueOnDarkBlue');

        $map = ThemeRegistry::getRegistry()->getMap();
        $this->assertEquals(0, count($map['itemsTest']['available'])); //no themes left in itemsTest
        $this->assertEquals(0, count($map['testsTest']['available'])); //no themes left in testsTest
    }

    //
    //Negative tests follow
    //

    public function testRegisterThemeNoTarget()
    {
        $this->expectException(common_Exception::class);
        $this->expectExceptionMessage('Target itemsTest does not exist');
        ThemeRegistry::getRegistry()->registerTheme('blackOnLightMagenta', 'Black on Light Magenta', 'blackOnLightMagenta', ['itemsTest']);
    }

    public function testRegisterThemeInvalidId()
    {
        $this->expectException(common_Exception::class);
        $this->expectExceptionMessage('Invalid id');
        ThemeRegistry::getRegistry()->createTarget('itemsTest', 'base');
        ThemeRegistry::getRegistry()->registerTheme('?*invalid theme-id*?', 'Black on Light Magenta', 'blackOnLightMagenta', ['itemsTest']);
    }

    public function testRegisterThemeDuplicate()
    {
        $this->expectException(common_Exception::class);
        $this->expectExceptionMessage('already exists for target');
        ThemeRegistry::getRegistry()->createTarget('itemsTest', 'base');
        ThemeRegistry::getRegistry()->registerTheme('blackOnLightMagenta', 'Black on Light Magenta', 'blackOnLightMagenta', ['itemsTest']);
        ThemeRegistry::getRegistry()->registerTheme('blackOnLightMagenta', 'Black on Light Magenta', 'blackOnLightMagenta', ['itemsTest']);
    }


    public function testRegisterThemeNoTargets()
    {
        $this->expectException(common_Exception::class);
        $this->expectExceptionMessage('No targets were provided');
        ThemeRegistry::getRegistry()->createTarget('itemsTest', 'base');
        ThemeRegistry::getRegistry()->registerTheme('blackOnLightMagenta', 'Black on Light Magenta', 'blackOnLightMagenta');
    }

    public function testSetDefaultThemeNoTarget()
    {
        $this->expectException(common_Exception::class);
        $this->expectExceptionMessage('Target testsTest does not exist');
        ThemeRegistry::getRegistry()->createTarget('itemsTest', 'base');
        ThemeRegistry::getRegistry()->registerTheme('blackOnLightMagenta', 'Black on Light Magenta', 'blackOnLightMagenta', ['itemsTest']);
        ThemeRegistry::getRegistry()->registerTheme('lightBlueOnDarkBlue', 'Light Blue on Dark Blue', 'lightBlueOnDarkBlue', ['itemsTest']);

        ThemeRegistry::getRegistry()->setDefaultTheme('testsTest', 'blackOnLightMagenta');
    }

    public function testSetDefaultThemeNoTheme()
    {
        $this->expectException(common_Exception::class);
        $this->expectExceptionMessage('Theme blackOnLightMagenta not found for target testsTest');
        ThemeRegistry::getRegistry()->createTarget('itemsTest', 'base');
        ThemeRegistry::getRegistry()->createTarget('testsTest', 'base');
        ThemeRegistry::getRegistry()->registerTheme('blackOnLightMagenta', 'Black on Light Magenta', 'blackOnLightMagenta', ['itemsTest']);
        ThemeRegistry::getRegistry()->registerTheme('lightBlueOnDarkBlue', 'Light Blue on Dark Blue', 'lightBlueOnDarkBlue', ['itemsTest', 'testsTest']);

        ThemeRegistry::getRegistry()->setDefaultTheme('testsTest', 'blackOnLightMagenta');
    }

    public function testUnregisterThemeInvalidId()
    {
        $this->expectException(common_Exception::class);
        $this->expectExceptionMessage('Invalid id');
        ThemeRegistry::getRegistry()->unregisterTheme('?*invalid theme-id*?');
    }

    public function testUnregisterThemeNotFound()
    {
        $this->expectException(common_Exception::class);
        $this->expectExceptionMessage('Theme thisThemeDoesNotExist not found for any target');
        ThemeRegistry::getRegistry()->unregisterTheme('thisThemeDoesNotExist');
    }
}
