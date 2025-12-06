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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA
 *
 */

namespace oat\tao\test\unit\helpers;

use PHPUnit\Framework\TestCase;
use tao_helpers_Display;

/**
 * Test the class tao_helpers_Display
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
class DisplayTest extends TestCase
{
    /**
     * Test the method tao_helpers_Display::sanitizeXssHtml
     *
     * @dataProvider dirtyHtmlProvider
     */
    public function testSanitizeXssHtml($input, $expected)
    {
        $result = tao_helpers_Display::sanitizeXssHtml($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for testSanitizeXssHtml
     * @return array[] in the form of [input, expected]
     */
    public function dirtyHtmlProvider()
    {
        return [

            [null, null],
            ['', ''],
            ['foo', 'foo'],
            [
                '<b>foo</b> <span class="icon-foo"></span> <em>€©¾</em>',
                '<b>foo</b> <span class="icon-foo"></span> <em>€©¾</em>'
            ],
            ['<div><script>alert(\'foo\');</script></div>', '<div></div>'],
            ['<div>bar<script>alert(\'foo\');</script></div>', '<div>bar</div>'],
            ['<a href="http://taotesting.com">link</a>', '<a href="http://taotesting.com">link</a>'],
            [
                '<a href="http://taotesting.com" target="_blank">link</a>',
                '<a href="http://taotesting.com" target="_blank" rel="noreferrer noopener">link</a>'
            ],
            [
                '<div><img src="https://www.taotesting.com/wp-content/uploads/2014/09/oat-header-logo.png" '
                    . 'alt="logo" /></div>',
                '<div><img src="https://www.taotesting.com/wp-content/uploads/2014/09/oat-header-logo.png" '
                    . 'alt="logo" /></div>'
            ],
            ['<div><img src="javascript:alert(\'foo\');" /></div>', '<div></div>'],
            ['<div class="foo"><style>.foo { background-color: pink; }</style></div>', '<div class="foo"></div>'],
            ['<div><iframe src="http://taotesting.com"></iframe></div>', '<div></div>'],
        ];
    }
}
