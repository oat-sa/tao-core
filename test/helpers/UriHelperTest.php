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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\helpers\test;

use oat\tao\test\TaoPhpUnitTestRunner;

/**
 * Class UriHelperTest
 * @package oat\tao\helpers\test
 */
class UriHelperTest extends TaoPhpUnitTestRunner
{

    public function uriDataProvider()
    {
        return [
            ['index', 'Main', 'tao', ['param' => 'test 1'], '/tao/Main/index?param=test%201'],
            ['index', 'Main', 'tao', ['param' => 'test+1'], '/tao/Main/index?param=test%2B1'],
            ['index', 'Main', 'tao', ['param' => 'test+1 2'], '/tao/Main/index?param=test%2B1%202'],
            ['index', 'Main', 'tao', ['param' => 'test+1-2'], '/tao/Main/index?param=test%2B1-2'],
            ['index', 'Main', 'tao', ['param' => 'test - test'], '/tao/Main/index?param=test%20-%20test'],
            ['index', 'Main', 'tao', ['param' => 'multiple space case '], '/tao/Main/index?param=multiple%20space%20case'],
            ['index', 'Main', 'tao', ['param' => 'https://tao.test/TaoTest.rdf#i123123123123'], '/tao/Main/index?param=https%3A%2F%2Ftao.test%2FTaoTest.rdf%23i123123123123'],
            ['index', 'Main', 'tao', ['p1' => 't 1', 'p2' => 't 2'], '/tao/Main/index?p1=t%201&p2=t%202'],

        ];
    }

    /**
     * @dataProvider uriDataProvider
     */
    public function testUrlEncode($action, $module, $extension, $params, $expected)
    {
        $url = \tao_helpers_Uri::url($action, $module, $extension, $params);
        $this->assertContains($expected, $url);

    }

}