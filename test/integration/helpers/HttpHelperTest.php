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

namespace oat\tao\helpers\test;

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Stream;
use oat\tao\test\TaoPhpUnitTestRunner;
use Psr\Http\Message\ServerRequestInterface;
use tao_helpers_Http;

// phpcs:disable PSR1.Files.SideEffects
include_once dirname(__FILE__) . '/../../../includes/raw_start.php';
// phpcs:enable PSR1.Files.SideEffects

/**
 * @author Aleh Hutnikau hutnikau@qpt.com
 * @package tao
 */
class HttpHelperTest extends TaoPhpUnitTestRunner
{
    protected $string = '0123456789';

    public function setUp(): void
    {
        parent::setUp();
        TaoPhpUnitTestRunner::initTest();
    }

    /**
     * @dataProvider requestsProvider
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @param ServerRequestInterface $request
     * @param string $output
     */
    public function testReturnStream(ServerRequestInterface $request, string $output): void
    {
        ob_start();
        tao_helpers_Http::returnStream($this->getStream(), null, $request);
        $result = ob_get_clean();
        $this->assertEquals($output, $result);
    }

    private function getStream(): Stream
    {
        $resource = fopen('php://memory', 'r+');
        fwrite($resource, $this->string);
        rewind($resource);
        return new Stream($resource);
    }

    public function requestsProvider(): array
    {
        return [
            [
                new ServerRequest('POST', '/foo'),
                $this->string,
            ],
            [
                new ServerRequest('POST', '/foo', ['Range' => 'bytes=0-5']),
                '012345',
            ],
            [
                new ServerRequest('POST', '/foo', ['Range' => 'bytes=3-7']),
                '34567',
            ],
            [
                new ServerRequest('POST', '/foo', ['Range' => 'bytes=4-']),
                '456789',
            ],
            [
                new ServerRequest('POST', '/foo', ['Range' => 'bytes=-3']),
                '789',
            ],
            [
                new ServerRequest('POST', '/foo', ['Range' => 'bytes=0-0']),
                '0',
            ],
            [
                new ServerRequest('POST', '/foo', ['Range' => 'bytes=0-1,8-9']),
                '0189',
            ],
        ];
    }
}
