<?php declare(strict_types=1);
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

namespace oat\tao\test\unit\models\classes\accessControl\filter;

use oat\tao\model\accessControl\filter\JsonParameterFilter;
use oat\generis\test\TestCase;

class JsonParameterFilterTest extends TestCase
{
    /** @var JsonParameterFilter */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new JsonParameterFilter();
    }

    /**
     * @dataProvider filterProvider
     */
    public function testFilter(
        array $requestParameters,
        array $filterNames,
        array $expectedOutput
    ): void {
        $this->assertSame(
            $expectedOutput,
            $this->subject->filter($requestParameters, $filterNames)
        );
    }

    public function filterProvider(): array
    {
        return [
            'Empty parameters' => [
                'requestParameters' => [],
                'filterNames' => [],
                'expected' => [],
            ]
        ];
    }
}
