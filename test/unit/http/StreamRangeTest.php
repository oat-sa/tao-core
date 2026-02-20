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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

use GuzzleHttp\Psr7\Stream;
use oat\tao\model\stream\StreamRange;
use oat\tao\model\stream\StreamRangeException;

/**
 * Class StreamRangeTest
 * @package tao
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 */

use PHPUnit\Framework\TestCase;

class StreamRangeTest extends TestCase
{
    /**
     * @dataProvider rangesProvider
     */
    public function testConstruct($stream, $rangeValue, $first, $last)
    {
        $range = new StreamRange($stream, $rangeValue);
        $this->assertEquals($first, $range->getFirstPos());
        $this->assertEquals($last, $range->getLastPos());
    }

    /**
     * @dataProvider wrongRangesProvider
     * @param $stream
     * @param $rangeValue
     * @throws StreamRangeException
     */
    public function testConstructExcept($stream, $rangeValue)
    {
        $this->expectException(StreamRangeException::class);
        $range = new StreamRange($stream, $rangeValue);
    }


    public function wrongRangesProvider()
    {
        return [
            [
                'stream' => $this->getStream('0123456789'),
                'range' => '0-10', //last byte more than length
            ],
            [
                'stream' => $this->getStream('0123456789'),
                'range' => '-11', //offset from the end more than length
            ],
            [
                'stream' => $this->getStream('0123456789'),
                'range' => '10-', //first pos more than length
            ],
            [
                'stream' => $this->getStream('0123456789'),
                'range' => '10-11', //first pos more than length
            ],
            [
                'stream' => $this->getStream('0123456789'),
                'range' => '10-1', //first pos less than last
            ],
        ];
    }

    /**
     * @return array
     */
    public function rangesProvider()
    {
        return [
            [
                'stream' => $this->getStream('0123456789'),
                'range' => '0-9',
                'first' => 0,
                'last' => 9
            ],
            [
                'stream' => $this->getStream('0123456789'),
                'range' => '0-5',
                'first' => 0,
                'last' => 5
            ],
            [
                'stream' => $this->getStream('0123456789'),
                'range' => '5-9',
                'first' => 5,
                'last' => 9
            ],
            [
                'stream' => $this->getStream('0123456789'),
                'range' => '3-',
                'first' => 3,
                'last' => 9
            ],
            [
                'stream' => $this->getStream('0123456789'),
                'range' => '-3',
                'first' => 7,
                'last' => 9
            ],
            [
                'stream' => $this->getStream('0123456789'),
                'range' => '0-0',
                'first' => 0,
                'last' => 0
            ],
        ];
    }

    private function getStream(string $string): Stream
    {
        $resource = fopen('php://memory', 'r+');
        fwrite($resource, $string);
        rewind($resource);
        return new Stream($resource);
    }
}
