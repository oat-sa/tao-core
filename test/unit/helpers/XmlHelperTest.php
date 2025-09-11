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
 * Copyright (c) 2019  (original work) Open Assessment Technologies SA;
 *
 * @author Oleksandr Zagovorychev <zagovorichev@gmail.com>
 */

namespace oat\tao\test\unit\helpers;

use common_exception_Error;
use PHPUnit\Framework\TestCase;
use tao_helpers_Xml;

class XmlHelperTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testToArray(string $xml, array $expected): void
    {
        $actual = tao_helpers_Xml::to_array($xml);
        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider exceptionDataProvider
     */
    public function testToArrayExceptions(string $data, array $expected): void
    {
        try {
            tao_helpers_Xml::to_array($data);
            $this->fail('Should not be here, exceptions only');
        } catch (common_exception_Error $e) {
            $this->assertSame($expected['exception']['class'], get_class($e));
            $this->assertSame($expected['exception']['message'], $e->getMessage());
        }
    }

    /**
     * @dataProvider extractElementDataProvider
     */
    public function testExtractElements(string $xml, string $tag, string $namespace, array $expected): void
    {
        $elements = tao_helpers_Xml::extractElements($tag, $xml, $namespace);
        $this->assertSame($expected, $elements);
    }
    public function dataProvider(): array
    {
        return [
            [
                '<p>text</p>',
                ['text']
            ],
            [
                '<div><p>text</p></div>',
                [
                    'p' => 'text'
                ]
            ],
            [
                '<div><p><i>text</i></p></div>',
                [
                    'p' => [
                        'i' => 'text'
                    ]
                ]
            ],
            [
                "<div><a>first</a><br/><p></p><a>forth</a><p>test\n\nwith\t</p> anything else?</div>",
                [
                    'a' => [
                        'first',
                        'forth',
                    ],
                    'br' => [],
                    'p' => [
                        [],
                        "test\n\nwith\t",
                    ]
                ]
            ]
        ];
    }

    public function exceptionDataProvider(): array
    {
        return [
            // xml validation
            [
                'just a string',
                [
                    'exception' => [
                        'class' => common_exception_Error::class,
                        'message' => 'Start tag expected, \'<\' not found [1]'
                    ],
                ],
            ],
            // without a root element
            [
                '<p>text</p><p></p><br/><a> </a>',
                [
                    'exception' => [
                        'class' => common_exception_Error::class,
                        'message' => 'Extra content at the end of the document [1]'
                    ]
                ]
            ],
            [
                '<aasdf</a>',
                [
                    'exception' => [
                        'class' => common_exception_Error::class,
                        'message' => "error parsing attribute name [1]\nattributes construct error [1]\nCouldn't find "
                            . "end of Start Tag aasdf line 1 [1]\nExtra content at the end of the document [1]"
                    ]
                ]
            ]
        ];
    }

    public function extractElementDataProvider(): array
    {
        return [
            [
                '<p>some text <a>link 1</a> and <a>link 2</a></p>', // xml
                'a', // tag to be extracted
                '', // namespace
                [
                    'link 1',
                    'link 2',
                ], // result
            ],
            // with namespace
            [
                '<p>some text <a>link 1</a> and <a xmlns="http://www.w3.org/1999/xhtml">link 2</a></p>', // xml
                'a', // tag to be extracted
                'http://www.w3.org/1999/xhtml', // namespace
                [
                    'link 2',
                ], // result
            ],
        ];
    }
}
