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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\helpers;

use PHPUnit\Framework\TestCase;
use tao_helpers_Date as DateHelper;

class DateTest extends TestCase
{
    /**
     * @dataProvider microtimeProvider
     */
    public function testMicrotimeFormat(?string $microtime, ?string $expected)
    {
        $result = DateHelper::formatMicrotime($microtime);
        $this->assertEquals($expected, $result);
    }

    public function microtimeProvider()
    {
        return [
            ['microtime' => '0.51487900 1701076034', 'expected' => '2023-11-27T09:07:14.514+00:00'],
            ['microtime' => '0.81487900 1701078054', 'expected' => '2023-11-27T09:40:54.814+00:00'],
            ['microtime' => '0.71487900 1701073054', 'expected' => '2023-11-27T08:17:34.714+00:00'],
            ['microtime' => '0.0 1701073054', 'expected' => '2023-11-27T08:17:34.000+00:00'],
            ['microtime' => '0 1701073054', 'expected' => '2023-11-27T08:17:34.000+00:00'],
            ['microtime' => null, 'expected' => null]
        ];
    }
}
