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
 * Copyright (c) 2020  (original work) Open Assessment Technologies SA;
 *
 * @author Oleksandr Zagovorychev <zagovorichev@gmail.com>
 */
declare(strict_types=1);

namespace oat\tao\test\unit\models\layout;

use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\tao\model\layout\configuredLayout\AbstractLayoutPageTitleService;
use Request;

class TestPageTitle extends AbstractLayoutPageTitleService
{
    protected function getMap(): array
    {
        return [
            'controller1' => [
                'action1' => [
                    'request' => [
                        'param1' => 'value1',
                    ],
                    'title' => 'title1',
                ]
            ],
            'controller2' => [
                'action2' => 'title2',
            ],
            'controller4' => 'title4',
        ];
    }
}

class AbstractLayoutPageTitleServiceTest extends TestCase
{

    /**
     * @var TestPageTitle
     */
    private $pageTitleService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pageTitleService = new TestPageTitle();
    }

    public function dataProvider(): array
    {
        return [
            [
                'controller1',
                'action1',
                ['param1' => 'value1'],
                'title1',
            ],
            [
                'controller1',
                'action1',
                ['param1' => 'value2'],
                null,
            ],
            [
                'controller1',
                'action2',
                ['param1' => 'value1'],
                null,
            ],
            [
                'controller1',
                'action1',
                ['param2' => 'value1'],
                null,
            ],
            [
                'controller2',
                'action1',
                ['param1' => 'value1'],
                null,
            ],
            [
                'controller2',
                'action2',
                ['param2' => 'value2'],
                'title2',
            ],
            [
                'controller3',
                '',
                [],
                null,
            ],
            [
                'controller3',
                '',
                ['param3' => 'value3'],
                null,
            ],
            [
                'controller4',
                '',
                ['param4' => 'value4'],
                'title4',
            ],
            [
                'controller4',
                '',
                [],
                'title4',
            ]
        ];
    }

    /**
     * @dataProvider dataProvider
     * @param string $controllerName
     * @param string $actionName
     * @param array $requestParams
     * @param $expected
     */
    public function testGetTitle(string $controllerName, string $actionName, array $requestParams, $expected): void
    {
        /** @var Request|MockObject $request */
        $request = new Request();
        $request->addParameters($requestParams);
        $this->assertSame($expected, $this->pageTitleService->getTitle($controllerName, $actionName, $request));
    }
}
