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

namespace oat\tao\test\unit\model\Lists\Business\Domain;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use oat\tao\model\Lists\Business\Domain\DependencyRepositoryContext;

class DependencyRepositoryContextTest extends TestCase
{
    public function testSupportedParameters(): void
    {
        $context = new DependencyRepositoryContext(
            [
                DependencyRepositoryContext::PARAM_LIST_URIS => ['listUri'],
                DependencyRepositoryContext::PARAM_DEPENDENCY_LIST_VALUES => ['listValue'],
            ]
        );

        $this->assertEquals(
            ['listUri'],
            $context->getParameter(DependencyRepositoryContext::PARAM_LIST_URIS)
        );
        $this->assertEquals(
            ['listValue'],
            $context->getParameter(DependencyRepositoryContext::PARAM_DEPENDENCY_LIST_VALUES)
        );
    }

    /**
     * @dataProvider invalidValues
     */
    public function testCreationWithInvalidValues(string $parameter, array $invalidValues): void
    {
        $numberOfExceptions = 0;

        foreach ($invalidValues as $invalidValue) {
            try {
                new DependencyRepositoryContext(
                    [
                        $parameter => $invalidValue,
                    ]
                );
            } catch (InvalidArgumentException $exception) {
                $this->assertContainsEquals(
                    $exception->getMessage(),
                    [
                        sprintf(
                            'Context parameter %s is not valid. It should be an array.',
                            $parameter
                        ),
                        sprintf(
                            'Context parameter %s is not valid. The values must be a string.',
                            $parameter
                        ),
                    ]
                );
                ++$numberOfExceptions;
            }
        }

        $this->assertEquals(count($invalidValues), $numberOfExceptions);
    }

    public function invalidValues(): array
    {
        $invalidValues = [
            new class () {
            },
            'string',
            123,
            null,
            [new class () {
            }],
            [[]],
            [123],
            [null],
        ];

        return [
            'List URIs' => [
                'parameter' => DependencyRepositoryContext::PARAM_LIST_URIS,
                'invalidValues' => $invalidValues,
            ],
            'List values' => [
                'parameter' => DependencyRepositoryContext::PARAM_DEPENDENCY_LIST_VALUES,
                'invalidValues' => $invalidValues,
            ],
        ];
    }
}
