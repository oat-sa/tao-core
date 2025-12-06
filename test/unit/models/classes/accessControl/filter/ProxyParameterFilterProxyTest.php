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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\accessControl\filter;

use PHPUnit\Framework\TestCase;
use oat\tao\model\accessControl\filter\ParameterFilterInterface;
use oat\tao\model\accessControl\filter\ParameterFilterProxy;

class ProxyParameterFilterProxyTest extends TestCase
{
    /** @var ParameterFilterProxy */
    private $subject;

    /** @var ParameterFilterInterface */
    private $filter1;

    /** @var ParameterFilterInterface */
    private $filter2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filter1 = $this->createMock(ParameterFilterInterface::class);
        $this->filter2 = $this->createMock(ParameterFilterInterface::class);
        $this->subject = new ParameterFilterProxy(...[$this->filter1, $this->filter2]);
    }

    /**
     * @dataProvider filterProvider
     */
    public function testFilter(
        array $filter1Output,
        array $filter2Output,
        array $expectedOutput
    ): void {
        $this->filter1
            ->method('filter')
            ->willReturn($filter1Output);

        $this->filter2
            ->method('filter')
            ->willReturn($filter2Output);

        $this->assertSame(
            $expectedOutput,
            $this->subject->filter([], [])
        );
    }

    public function filterProvider(): array
    {
        return [
            'Empty parameters' => [
                'filter1Output' => [],
                'filter2Output' => [],
                'expected' => [],
            ],
            'Consider filter 1 URIs' => [
                'filter1Output' => ['filer1Uri'],
                'filter2Output' => ['filer2Uri'],
                'expected' => ['filer1Uri'],
            ],
            'Consider filter 2 URIs' => [
                'filter1Output' => [],
                'filter2Output' => ['filer2Uri'],
                'expected' => ['filer2Uri'],
            ]
        ];
    }
}
