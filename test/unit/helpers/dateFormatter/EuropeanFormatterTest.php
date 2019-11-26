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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\tao\test\unit\helpers\dateFormatter;

use oat\generis\test\TestCase;
use oat\tao\helpers\dateFormatter\EuropeanFormatter;
use tao_helpers_Date as DateHelper;

class EuropeanFormatterTest extends TestCase
{
    /**
     * @dataProvider formatsToTest
     */
    public function testGetFormat($format, $expected): void
    {
        $subject = new EuropeanFormatter();

        $this->assertSame($expected, $subject->getFormat($format));
    }

    public function formatsToTest()
    {
        return [
            [DateHelper::FORMAT_LONG, 'd/m/Y H:i:s'],
            [DateHelper::FORMAT_LONG_MICROSECONDS, 'd/m/Y H:i:s.u'],
            [DateHelper::FORMAT_DATEPICKER, 'Y-m-d H:i'],
            [DateHelper::FORMAT_VERBOSE, 'F j, Y, g:i:s a'],
            [DateHelper::FORMAT_ISO8601, 'Y-m-d\TH:i:s.v'],
        ];
    }

    /**
     * @dataProvider javascriptFormatsToTest
     */
    public function testGetJavascriptFormat($format, $expected): void
    {
        $subject = new EuropeanFormatter();

        $this->assertSame($expected, $subject->getJavascriptFormat($format));
    }

    public function javascriptFormatsToTest()
    {
        return [
            [DateHelper::FORMAT_LONG, 'DD/MM/YYYY HH:mm:ss'],
            [DateHelper::FORMAT_LONG_MICROSECONDS, 'DD/MM/YYYY HH:mm:ss.SSSSSS'],
            [DateHelper::FORMAT_DATEPICKER, 'YYYY-MM-DD HH:mm'],
            [DateHelper::FORMAT_VERBOSE, 'MMMM D, YYYY, h:mm:ss a'],
            [DateHelper::FORMAT_ISO8601, 'YYYY-MM-DD[T]HH:mm:ss.SSS'],
        ];
    }
}
