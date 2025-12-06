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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA
 */

namespace oat\tao\test\unit\http;

use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use oat\tao\model\http\RequestRebuilder;

class RequestRebuilderTest extends TestCase
{
    /**
     * @var RequestRebuilder
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new RequestRebuilder();
    }

    /**
     * @dataProvider urlHeaderProvider
     */
    public function testRebuild(string $url, array $headers, string $expectedUrl): void
    {
        $request = $this->subject->rebuild(new Request('get', $url, $headers));
        $this->assertEquals($expectedUrl, (string)$request->getUri());
    }


    public function urlHeaderProvider(): array
    {
        return [
            ['http://tao.lu', [], 'http://tao.lu'],
            ['https://tao.lu', [], 'https://tao.lu'],
            [
                'http://tao.lu',
                ['x-forwarded-proto' => 'https'],
                'https://tao.lu'
            ],
            [
                'http://tao.lu',
                ['x-forwarded-proto' => null],
                'http://tao.lu'
            ],
            [
                'http://tao.lu',
                ['x-forwarded-ssl' => 'on'],
                'https://tao.lu'
            ],
            [
                'http://tao.lu',
                ['x-forwarded-ssl' => null],
                'http://tao.lu'
            ],
        ];
    }
}
