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
use core_kernel_classes_Property;
use oat\tao\model\Lists\Business\Domain\DependsOnPropertySynchronizerContext;

class DependsOnPropertySynchronizerContextTest extends TestCase
{
    public function testSupportedParameters(): void
    {
        $supportedParameters = [
            DependsOnPropertySynchronizerContext::PARAM_PROPERTIES => [
                $this->createMock(
                    core_kernel_classes_Property::class
                ),
            ],
        ];

        $numberOfExceptions = 0;

        try {
            new DependsOnPropertySynchronizerContext($supportedParameters);
        } catch (InvalidArgumentException $exception) {
            ++$numberOfExceptions;
        }

        $sut = new DependsOnPropertySynchronizerContext([]);

        foreach ($supportedParameters as $parameter => $value) {
            try {
                $sut->setParameter($parameter, $value);
            } catch (InvalidArgumentException $exception) {
                ++$numberOfExceptions;
            }
        }

        $this->assertEquals(0, $numberOfExceptions);
    }

    public function testUnsupportedParameter(): void
    {
        $numberOfExceptions = 0;

        // Test creation with unsupported parameter
        try {
            new DependsOnPropertySynchronizerContext([
                'unsupportedParameter' => 'test',
            ]);
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals(
                'Context parameter unsupportedParameter is not supported.',
                $exception->getMessage()
            );
            ++$numberOfExceptions;
        }

        $sut = new DependsOnPropertySynchronizerContext([]);

        // Test getter of unsupported parameter
        try {
            $sut->getParameter('unsupportedParameter');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals(
                'Context parameter unsupportedParameter is not supported.',
                $exception->getMessage()
            );
            ++$numberOfExceptions;
        }

        // Test setter of unsupported parameter
        try {
            $sut->setParameter('unsupportedParameter', 'value');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals(
                'Context parameter unsupportedParameter is not supported.',
                $exception->getMessage()
            );
            ++$numberOfExceptions;
        }

        $this->assertEquals(3, $numberOfExceptions);
    }

    /**
     * @dataProvider invalidValues
     */
    public function testCreationWithInvalidValues(string $parameter, array $invalidValues): void
    {
        $numberOfExceptions = 0;

        foreach ($invalidValues as $invalidValue) {
            try {
                new DependsOnPropertySynchronizerContext([
                    $parameter => $invalidValue,
                ]);
            } catch (InvalidArgumentException $exception) {
                $this->assertContainsEquals(
                    $exception->getMessage(),
                    [
                        'Context parameter properties is not valid. It should be an array.',
                        'Context parameter properties is not valid. Values must be an instance of '
                            . 'core_kernel_classes_Property.',
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
        $sut = new DependsOnPropertySynchronizerContext([]);

        foreach ($invalidValues as $invalidValue) {
            try {
                $sut->setParameter($parameter, $invalidValue);
            } catch (InvalidArgumentException $exception) {
                $this->assertContainsEquals(
                    $exception->getMessage(),
                    [
                        'Context parameter properties is not valid. It should be an array.',
                        'Context parameter properties is not valid. Values must be an instance of '
                            . 'core_kernel_classes_Property.',
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
            DependsOnPropertySynchronizerContext::PARAM_PROPERTIES => [
                $this->createMock(
                    core_kernel_classes_Property::class
                ),
            ],
        ];

        $sut = new DependsOnPropertySynchronizerContext($parametersValues);

        foreach ($parametersValues as $parameter => $value) {
            $this->assertEquals($value, $sut->getParameter($parameter));
        }
    }

    /**
     * @dataProvider validValues
     */
    public function testSetAndGetValues(string $parameter, array $validValues): void
    {
        $sut = new DependsOnPropertySynchronizerContext([]);

        $this->assertNull($sut->getParameter($parameter));

        foreach ($validValues as $validValue) {
            $sut->setParameter($parameter, $validValue);
            $this->assertEquals($validValue, $sut->getParameter($parameter));
        }
    }

    public function invalidValues(): array
    {
        $class = new class () {
        };

        return [
            'Properties' => [
                'parameter' => DependsOnPropertySynchronizerContext::PARAM_PROPERTIES,
                'invalidValues' => [
                    $class,
                    'string',
                    123,
                    null,
                    [$class],
                    ['json'],
                    [123],
                    [null],
                ],
            ],
        ];
    }

    public function validValues(): array
    {
        return [
            'Properties' => [
                'parameter' => DependsOnPropertySynchronizerContext::PARAM_PROPERTIES,
                'values' => [
                    [],
                    [
                        $this->createMock(
                            core_kernel_classes_Property::class
                        ),
                    ],
                ],
            ],
        ];
    }
}
