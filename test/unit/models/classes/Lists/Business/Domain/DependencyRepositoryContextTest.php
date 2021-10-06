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
use oat\generis\test\TestCase;
use oat\tao\model\Lists\Business\Domain\DependencyRepositoryContext;

class DependencyRepositoryContextTest extends TestCase
{
    public function testSupportedParameters(): void
    {
        $supportedParameters = [
            DependencyRepositoryContext::PARAM_LIST_URIS => ['listUri'],
            DependencyRepositoryContext::PARAM_DEPENDENCY_LIST_VALUES => ['listValue'],
        ];

        $numberOfExceptions = 0;

        try {
            new DependencyRepositoryContext($supportedParameters);
        } catch (InvalidArgumentException $exception) {
            ++$numberOfExceptions;
        }

        $sut = new DependencyRepositoryContext([]);

        foreach ($supportedParameters as $parameter => $value) {
            try {
                $sut->setParameter($parameter, $value);
            } catch (InvalidArgumentException $exception) {
                ++$numberOfExceptions;
            }
        }

        $this->assertEquals(0, $numberOfExceptions);
    }

    /**
     * @dataProvider invalidValues
     */
    public function testCreationWithInvalidValues(string $parameter, array $invalidValues): void
    {
        $numberOfExceptions = 0;

        foreach ($invalidValues as $invalidValue) {
            try {
                new DependencyRepositoryContext([
                    $parameter => $invalidValue,
                ]);
            } catch (InvalidArgumentException $exception) {
                $this->assertContainsEquals(
                    $exception->getMessage(),
                    [
                        sprintf(
                            'Context parameter %s is not valid. The values must be a string.',
                            $parameter
                        ),
                        sprintf(
                            'Context parameter %s is not valid.',
                            $parameter
                        ),
                    ]
                );
                ++$numberOfExceptions;
            }
        }

        $this->assertEquals(count($invalidValues), $numberOfExceptions);
    }

    /**
     * @dataProvider invalidValues
     */
    public function testSetInvalidValues(string $parameter, array $invalidValues): void
    {
        $numberOfExceptions = 0;
        $sut = new DependencyRepositoryContext([]);

        foreach ($invalidValues as $invalidValue) {
            try {
                $sut->setParameter($parameter, $invalidValue);
            } catch (InvalidArgumentException $exception) {
                $this->assertContainsEquals(
                    $exception->getMessage(),
                    [
                        sprintf(
                            'Context parameter %s is not valid. The values must be a string.',
                            $parameter
                        ),
                        sprintf(
                            'Context parameter %s is not valid.',
                            $parameter
                        ),
                    ]
                );
                ++$numberOfExceptions;
            }
        }

        $this->assertEquals(count($invalidValues), $numberOfExceptions);
    }

    public function testCreateAndGetValues(): void
    {
        $parametersValues = [
            DependencyRepositoryContext::PARAM_LIST_URIS => ['listUri'],
            DependencyRepositoryContext::PARAM_DEPENDENCY_LIST_VALUES => ['listValue']
        ];

        $sut = new DependencyRepositoryContext($parametersValues);

        foreach ($parametersValues as $parameter => $value) {
            $this->assertEquals($value, $sut->getParameter($parameter));
        }
    }

    /**
     * @dataProvider validValues
     */
    public function testSetAndGetValues(string $parameter, array $validValues): void
    {
        $sut = new DependencyRepositoryContext([]);

        $this->assertNull($sut->getParameter($parameter));

        foreach ($validValues as $validValue) {
            $sut->setParameter($parameter, $validValue);
            $this->assertEquals($validValue, $sut->getParameter($parameter));
        }
    }

    public function invalidValues(): array
    {
        $object = new class() {};

        return [
            'List URIs' => [
                'parameter' => DependencyRepositoryContext::PARAM_LIST_URIS,
                'invalidValues' => [
                    $object,
                    'string',
                    123,
                    null,
                    [$object],
                    [[]],
                    [123],
                    [null],
                ],
            ],
            'List values' => [
                'parameter' => DependencyRepositoryContext::PARAM_DEPENDENCY_LIST_VALUES,
                'invalidValues' => [
                    $object,
                    'string',
                    123,
                    null,
                    [$object],
                    [[]],
                    [123],
                    [null],
                ],
            ],
        ];
    }

    public function validValues(): array
    {
        return [
            'List URIs' => [
                'parameter' => DependencyRepositoryContext::PARAM_LIST_URIS,
                'values' => [
                    [],
                    ['string']
                ],
            ],
            'List values' => [
                'parameter' => DependencyRepositoryContext::PARAM_DEPENDENCY_LIST_VALUES,
                'values' => [
                    [],
                    ['string']
                ],
            ],
        ];
    }
}
