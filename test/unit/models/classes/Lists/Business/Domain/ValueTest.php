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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 *
 * @author Sergei Mikhailov <sergei.mikhailov@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\Lists\Business\Domain;

use oat\generis\test\TestCase;
use oat\tao\model\Lists\Business\Domain\Value;

class ValueTest extends TestCase
{
    /**
     * @param string $uri
     * @param string $label
     * @param array  $expected
     *
     * @dataProvider dataProvider
     */
    public function testSerialization(string $uri, string $label, array $expected): void
    {
        $this->assertJsonStringEqualsJsonString(
            json_encode($expected),
            json_encode((new Value($uri, $label)))
        );
    }

    /**
     * @param string $uri
     * @param string $label
     *
     * @dataProvider dataProvider
     */
    public function testAccessors(string $uri, string $label): void
    {
        $sut = new Value($uri, $label);

        $this->assertSame($uri, $sut->getUri());
        $this->assertSame($label, $sut->getLabel());
    }

    public function dataProvider(): array
    {
        return [
            'Domain only URI'            => [
                'uri'      => 'https://example.com',
                'label'    => 'test value, it can have https://anything.it/needs#1',
                'expected' => [
                    'uri'   => 'https_2_example_0_com',
                    'label' => 'test value, it can have https://anything.it/needs#1',
                ],
            ],
            'Domain with port URI'       => [
                'uri'      => 'https://example.com:80',
                'label'    => 'test value',
                'expected' => [
                    'uri'   => 'https_2_example_0_com_4_80',
                    'label' => 'test value',
                ],
            ],
            'URI with path'              => [
                'uri'      => 'https://example.com/path',
                'label'    => 'test value',
                'expected' => [
                    'uri'   => 'https_2_example_0_com_1_path',
                    'label' => 'test value',
                ],
            ],
            'URI with path and fragment' => [
                'uri'      => 'https://example.com/path#fragment',
                'label'    => 'test value',
                'expected' => [
                    'uri'   => 'https_2_example_0_com_1_path_3_fragment',
                    'label' => 'test value',
                ],
            ],
        ];
    }
}
