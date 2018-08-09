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

use oat\tao\test\TaoPhpUnitTestRunner;
use tao_helpers_Http;
use Slim\Http\Stream;
use Slim\Http\Environment;
use Slim\Http\Request;

include_once dirname(__FILE__) . '/../../../includes/raw_start.php';

/**
 * @author Aleh Hutnikau hutnikau@qpt.com
 * @package tao
 */
class HttpHelperTest extends TaoPhpUnitTestRunner
{

    protected $string = '0123456789';

    public function setUp()
    {
        parent::setUp();
        TaoPhpUnitTestRunner::initTest();
    }

    /**
     * @dataProvider environmentsProvider
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @param Environment $env
     * @param string $output
     */
    public function testReturnStream($env, $output)
    {
        $request = Request::createFromEnvironment($env);
        ob_start();
        tao_helpers_Http::returnStream($this->getStream(), null, $request);
        $result = ob_get_clean();
        $this->assertEquals($output, $result);
    }

    /**
     * @return Stream
     */
    private function getStream()
    {
        $resource = fopen('php://memory','r+');
        fwrite($resource, $this->string);
        rewind($resource);
        return new Stream($resource);
    }

    public function environmentsProvider()
    {
        return [
            [
                'env' => Environment::mock([
                    'SCRIPT_NAME' => '/index.php',
                    'REQUEST_URI' => '/foo',
                    'REQUEST_METHOD' => 'POST',
                ]),
                'output' => $this->string,
            ],
            [
                'env' => Environment::mock([
                    'SCRIPT_NAME' => '/index.php',
                    'REQUEST_URI' => '/foo',
                    'REQUEST_METHOD' => 'POST',
                    'HTTP_RANGE' => 'bytes=0-5',
                ]),
                'output' => '012345',
            ],
            [
                'env' => Environment::mock([
                    'SCRIPT_NAME' => '/index.php',
                    'REQUEST_URI' => '/foo',
                    'REQUEST_METHOD' => 'POST',
                    'HTTP_RANGE' => 'bytes=3-7',
                ]),
                'output' => '34567',
            ],
            [
                'env' => Environment::mock([
                    'SCRIPT_NAME' => '/index.php',
                    'REQUEST_URI' => '/foo',
                    'REQUEST_METHOD' => 'POST',
                    'HTTP_RANGE' => 'bytes=4-',
                ]),
                'output' => '456789',
            ],
            [
                'env' => Environment::mock([
                    'SCRIPT_NAME' => '/index.php',
                    'REQUEST_URI' => '/foo',
                    'REQUEST_METHOD' => 'POST',
                    'HTTP_RANGE' => 'bytes=-3',
                ]),
                'output' => '789',
            ],
            [
                'env' => Environment::mock([
                    'SCRIPT_NAME' => '/index.php',
                    'REQUEST_URI' => '/foo',
                    'REQUEST_METHOD' => 'POST',
                    'HTTP_RANGE' => 'bytes=0-0',
                ]),
                'output' => '0',
            ],
            [
                'env' => Environment::mock([
                    'SCRIPT_NAME' => '/index.php',
                    'REQUEST_URI' => '/foo',
                    'REQUEST_METHOD' => 'POST',
                    'HTTP_RANGE' => 'bytes=0-1,8-9',
                ]),
                'output' => '0189',
            ],
        ];
    }

}
