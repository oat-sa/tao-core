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
use oat\tao\model\Lists\Business\Domain\DependentPropertiesRepositoryContext;

class DependentPropertiesRepositoryContextTest extends TestCase
{
    public function testSupportedParameters(): void
    {
        $supportedParameters = [
            DependentPropertiesRepositoryContext::PARAM_PROPERTY => $this->createMock(
                core_kernel_classes_Property::class
            ),
        ];

        $numberOfExceptions = 0;

        try {
            new DependentPropertiesRepositoryContext($supportedParameters);
        } catch (InvalidArgumentException $exception) {
            ++$numberOfExceptions;
        }

        $sut = new DependentPropertiesRepositoryContext([]);

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
            new DependentPropertiesRepositoryContext([
                'unsupportedParameter' => 'test',
            ]);
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals(
                'Context parameter unsupportedParameter is not supported.',
                $exception->getMessage()
            );
            ++$numberOfExceptions;
        }

        $sut = new DependentPropertiesRepositoryContext([]);

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
                new DependentPropertiesRepositoryContext([
                    $parameter => $invalidValue,
                ]);
            } catch (InvalidArgumentException $exception) {
                $this->assertEquals(
                    sprintf(
                        'Context parameter %s is not valid. It must be an instance of core_kernel_classes_Property.',
                        $parameter
                    ),
                    $exception->getMessage()
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
        $sut = new DependentPropertiesRepositoryContext([]);

        foreach ($invalidValues as $invalidValue) {
            try {
                $sut->setParameter($parameter, $invalidValue);
            } catch (InvalidArgumentException $exception) {
                $this->assertEquals(
                    sprintf(
                        'Context parameter %s is not valid. It must be an instance of core_kernel_classes_Property.',
                        $parameter
                    ),
                    $exception->getMessage()
                );
                ++$numberOfExceptions;
            }
        }

        $this->assertEquals(count($invalidValues), $numberOfExceptions);
    }

    public function testCreateAndGetValues(): void
    {
        $parametersValues = [
            DependentPropertiesRepositoryContext::PARAM_PROPERTY => $this->createMock(
                core_kernel_classes_Property::class
            ),
        ];

        $sut = new DependentPropertiesRepositoryContext($parametersValues);

        foreach ($parametersValues as $parameter => $value) {
            $this->assertEquals($value, $sut->getParameter($parameter));
        }
    }

    /**
     * @dataProvider validValues
     */
    public function testSetAndGetValues(string $parameter, array $validValues): void
    {
        $sut = new DependentPropertiesRepositoryContext([]);

        $this->assertNull($sut->getParameter($parameter));

        foreach ($validValues as $validValue) {
            $sut->setParameter($parameter, $validValue);
            $this->assertEquals($validValue, $sut->getParameter($parameter));
        }
    }

    public function invalidValues(): array
    {
        return [
            'Property' => [
                'parameter' => DependentPropertiesRepositoryContext::PARAM_PROPERTY,
                'invalidValues' => [
                    new class () {
                    },
                    [],
                    ['json'],
                    'string',
                    123,
                    null,
                ],
            ],
        ];
    }

    public function validValues(): array
    {
        return [
            'Property' => [
                'parameter' => DependentPropertiesRepositoryContext::PARAM_PROPERTY,
                'values' => [
                    $this->createMock(
                        core_kernel_classes_Property::class
                    ),
                ],
            ],
        ];
    }
}
