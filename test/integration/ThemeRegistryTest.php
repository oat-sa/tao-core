<?php

declare(strict_types=1);

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
 */

namespace oat\tao\test\integration;

use oat\generis\test\GenerisPhpUnitTestRunner;
use oat\tao\model\ThemeRegistry;



/**
 * @author Lionel Lecaque, lionel@taotesting.com
 */
class ThemeRegistryTest extends GenerisPhpUnitTestRunner
{
    protected function tearDown(): void
    {
        parent::tearDown();

        ThemeRegistry::getRegistry()->remove('itemsTest');
        ThemeRegistry::getRegistry()->remove('testsTest');
    }

    public function testsetDefaultTheme(): void
    {
        ThemeRegistry::getRegistry()->createTarget('itemsTest', 'base');
        ThemeRegistry::getRegistry()->registerTheme('lightBlueOnDarkBlue', 'Light Blue on Dark Bluea', 'path', ['itemsTest']);
        ThemeRegistry::getRegistry()->setDefaultTheme('itemsTest', 'lightBlueOnDarkBlue');

        $map = ThemeRegistry::getRegistry()->getMap();
        $this->assertFalse(empty($map));
        $this->assertInternalType('array', $map);

        $this->assertArrayHasKey('itemsTest', $map);

        $this->assertInternalType('array', $map['itemsTest']);
        $this->assertArrayHasKey('available', $map['itemsTest']);

        $available = current($map['itemsTest']['available']);
        $this->assertInternalType('array', $available);
        $this->assertArrayHasKey('name', $available);
        $this->assertSame('Light Blue on Dark Bluea', $available['name']);

        $defaultTheme = ThemeRegistry::getRegistry()->getDefaultTheme('itemsTest');
        $this->assertSame('lightBlueOnDarkBlue', $defaultTheme['id']);

        // target exist
        ThemeRegistry::getRegistry()->remove('itemsTest');
    }

    public function testRegister(): void
    {
        ThemeRegistry::getRegistry()->createTarget('itemsTest', 'base');
        ThemeRegistry::getRegistry()->registerTheme('blackOnLightMagenta', 'Black on Light Magenta', 'blackOnLightMagenta', ['itemsTest']);

        $map = ThemeRegistry::getRegistry()->getMap();
        $this->assertFalse(empty($map));
        $this->assertInternalType('array', $map);

        $this->assertArrayHasKey('itemsTest', $map);
        $this->assertInternalType('array', $map['itemsTest']);

        $this->assertInternalType('array', $map['itemsTest']);
        $this->assertArrayHasKey('available', $map['itemsTest']);

        $available = current($map['itemsTest']['available']);
        $this->assertInternalType('array', $available);
        $this->assertArrayHasKey('name', $available);

        $this->assertSame('Black on Light Magenta', $available['name']);

        ThemeRegistry::getRegistry()->createTarget('testsTest', 'base');
        ThemeRegistry::getRegistry()->registerTheme('lightBlueOnDarkBlue', 'Light Blue on Dark Blue', 'lightBlueOnDarkBlue', ['itemsTest', 'testsTest']);

        $map = ThemeRegistry::getRegistry()->getMap();

        $this->assertArrayHasKey('testsTest', $map);
        $this->assertInternalType('array', $map['testsTest']);

        $this->assertArrayHasKey('available', $map['testsTest']);

        $available = current($map['testsTest']['available']);
        $this->assertInternalType('array', $available);
        $this->assertArrayHasKey('name', $available);

        $this->assertSame('Light Blue on Dark Blue', $available['name']);

        foreach ($map['itemsTest']['available'] as $theme) {
            $this->assertInternalType('array', $theme);
            $this->assertArrayHasKey('id', $theme);
            $this->assertTrue(in_array($theme['id'], [
                'blackOnLightMagenta',
                'lightBlueOnDarkBlue',
            ], true));
        }

        ThemeRegistry::getRegistry()->registerTheme('superAccess', 'super accessibility theme', '', ['itemsTest'], ['tplA' => 'taoAccess/theme/A.tpl']);
        $map = ThemeRegistry::getRegistry()->getMap();
        $this->assertSame(3, count($map['itemsTest']['available']));
        $superAccessTheme = $map['itemsTest']['available'][2];

        $this->assertSame('superAccess', $superAccessTheme['id']);
        $this->assertSame(1, count($superAccessTheme['templates']));
        $this->assertSame('taoAccess/theme/A.tpl', $superAccessTheme['templates']['tplA']);
    }

    public function testGetTemplate(): void
    {
        ThemeRegistry::getRegistry()->createTarget('itemsTest', 'base');
        ThemeRegistry::getRegistry()->registerTheme('superAccess', 'super accessibility theme', '', ['itemsTest'], ['tplA' => 'taoAccess/theme/A.tpl']);
        ThemeRegistry::getRegistry()->registerTheme('superAccessNoTpl', 'super accessibility theme without tpl', '', ['itemsTest']);
        $this->assertNotEmpty(ThemeRegistry::getRegistry()->getTemplate('itemsTest', 'superAccess', 'tplA'));
        $this->assertEmpty(ThemeRegistry::getRegistry()->getTemplate('itemsTest', 'superAccess', 'tplB'));
        $this->assertEmpty(ThemeRegistry::getRegistry()->getTemplate('itemsTest', 'superAccessNoTpl', 'tplA'));
    }

    public function testUnregisterTheme(): void
    {
        ThemeRegistry::getRegistry()->createTarget('itemsTest', 'base');
        ThemeRegistry::getRegistry()->createTarget('testsTest', 'base');
        ThemeRegistry::getRegistry()->registerTheme('blackOnLightMagenta', 'Black on Light Magenta', 'blackOnLightMagenta', ['itemsTest']);
        ThemeRegistry::getRegistry()->registerTheme('lightBlueOnDarkBlue', 'Light Blue on Dark Blue', 'lightBlueOnDarkBlue', ['itemsTest', 'testsTest']);

        ThemeRegistry::getRegistry()->unregisterTheme('blackOnLightMagenta');

        $map = ThemeRegistry::getRegistry()->getMap();

        $this->assertArrayHasKey('itemsTest', $map);
        $this->assertSame(1, count($map['itemsTest']['available'])); //only one theme left
        $theme = current($map['itemsTest']['available']);
        $this->assertSame($theme['id'], 'lightBlueOnDarkBlue'); //and this theme is not the deleted one

        ThemeRegistry::getRegistry()->unregisterTheme('lightBlueOnDarkBlue');

        $map = ThemeRegistry::getRegistry()->getMap();
        $this->assertSame(0, count($map['itemsTest']['available'])); //no themes left in itemsTest
        $this->assertSame(0, count($map['testsTest']['available'])); //no themes left in testsTest
    }

    //
    //Negative tests follow
    //

    /**
     * @expectedException \common_Exception
     * @expectedExceptionMessage Target itemsTest does not exist
     */
    public function testRegisterThemeNoTarget(): void
    {
        ThemeRegistry::getRegistry()->registerTheme('blackOnLightMagenta', 'Black on Light Magenta', 'blackOnLightMagenta', ['itemsTest']);
    }

    /**
     * @expectedException \common_Exception
     * @expectedExceptionMessage Invalid id
     */
    public function testRegisterThemeInvalidId(): void
    {
        ThemeRegistry::getRegistry()->createTarget('itemsTest', 'base');
        ThemeRegistry::getRegistry()->registerTheme('?*invalid theme-id*?', 'Black on Light Magenta', 'blackOnLightMagenta', ['itemsTest']);
    }

    /**
     * @expectedException \common_Exception
     * @expectedExceptionMessage already exists for target
     */
    public function testRegisterThemeDuplicate(): void
    {
        ThemeRegistry::getRegistry()->createTarget('itemsTest', 'base');
        ThemeRegistry::getRegistry()->registerTheme('blackOnLightMagenta', 'Black on Light Magenta', 'blackOnLightMagenta', ['itemsTest']);
        ThemeRegistry::getRegistry()->registerTheme('blackOnLightMagenta', 'Black on Light Magenta', 'blackOnLightMagenta', ['itemsTest']);
    }

    /**
     * @expectedException \common_Exception
     * @expectedExceptionMessage No targets were provided
     */
    public function testRegisterThemeNoTargets(): void
    {
        ThemeRegistry::getRegistry()->createTarget('itemsTest', 'base');
        ThemeRegistry::getRegistry()->registerTheme('blackOnLightMagenta', 'Black on Light Magenta', 'blackOnLightMagenta');
    }

    /**
     * @expectedException \common_Exception
     * @expectedExceptionMessage Target testsTest does not exist
     */
    public function testSetDefaultThemeNoTarget(): void
    {
        ThemeRegistry::getRegistry()->createTarget('itemsTest', 'base');
        ThemeRegistry::getRegistry()->registerTheme('blackOnLightMagenta', 'Black on Light Magenta', 'blackOnLightMagenta', ['itemsTest']);
        ThemeRegistry::getRegistry()->registerTheme('lightBlueOnDarkBlue', 'Light Blue on Dark Blue', 'lightBlueOnDarkBlue', ['itemsTest']);

        ThemeRegistry::getRegistry()->setDefaultTheme('testsTest', 'blackOnLightMagenta');
    }

    /**
     * @expectedException \common_Exception
     * @expectedExceptionMessage Theme blackOnLightMagenta not found for target testsTest
     */
    public function testSetDefaultThemeNoTheme(): void
    {
        ThemeRegistry::getRegistry()->createTarget('itemsTest', 'base');
        ThemeRegistry::getRegistry()->createTarget('testsTest', 'base');
        ThemeRegistry::getRegistry()->registerTheme('blackOnLightMagenta', 'Black on Light Magenta', 'blackOnLightMagenta', ['itemsTest']);
        ThemeRegistry::getRegistry()->registerTheme('lightBlueOnDarkBlue', 'Light Blue on Dark Blue', 'lightBlueOnDarkBlue', ['itemsTest', 'testsTest']);

        ThemeRegistry::getRegistry()->setDefaultTheme('testsTest', 'blackOnLightMagenta');
    }

    /**
     * @expectedException \common_Exception
     * @expectedExceptionMessage Invalid id
     */
    public function testUnregisterThemeInvalidId(): void
    {
        ThemeRegistry::getRegistry()->unregisterTheme('?*invalid theme-id*?');
    }

    /**
     * @expectedException \common_Exception
     * @expectedExceptionMessage Theme thisThemeDoesNotExist not found for any target
     */
    public function testUnregisterThemeNotFound(): void
    {
        ThemeRegistry::getRegistry()->unregisterTheme('thisThemeDoesNotExist');
    }
}
