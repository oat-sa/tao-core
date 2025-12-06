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
use tao_helpers_Numeric;

/**
 * Test the class tao_helpers_Numeric
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
class NumericTest extends TestCase
{
    /**
     * Test the method tao_helpers_Numeric::parseFloat
     *
     * @dataProvider floatProvider
     */
    public function testParseFloat($input, $expected)
    {
        $result = tao_helpers_Numeric::parseFloat($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * Provides test case data
     * as input value / expected parsed float
     */
    public function floatProvider()
    {
        return [
            [ '0.1',           0.1 ],
            [ 0.1,             0.1 ],
            [ null,            0.0 ],
            [ false,           0.0 ],
            [ 'foo',           0.0 ],
            [ 'foo12.5',       12.5 ],
            [ '12.5foo',       12.5 ],
            [ '-27.541foo',    -27.541 ],
            [ 'bar-27.541foo', -27.541 ],
            [ '1 000,54',      1000.54 ],
            [ 1234,            1234.0 ],
            [ '02.34',         2.34 ],
            [ '02,340',        2.34 ],
        ];
    }
}
