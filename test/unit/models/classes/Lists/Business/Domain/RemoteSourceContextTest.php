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
use oat\tao\model\Lists\Business\Domain\RemoteSourceContext;

class RemoteSourceContextTest extends TestCase
{
    public function testSupportedParameters(): void
    {
        $supportedParameters = [
            RemoteSourceContext::PARAM_SOURCE_URL => 'Source URL',
            RemoteSourceContext::PARAM_URI_PATH => 'URI path',
            RemoteSourceContext::PARAM_LABEL_PATH => 'Label path',
            RemoteSourceContext::PARAM_DEPENDENCY_URI_PATH => 'Dependency URI path',
            RemoteSourceContext::PARAM_PARSER => 'Parser',
            RemoteSourceContext::PARAM_JSON => ['json'],
        ];

        $numberOfExceptions = 0;

        try {
            new RemoteSourceContext($supportedParameters);
        } catch (InvalidArgumentException $exception) {
            ++$numberOfExceptions;
        }

        $sut = new RemoteSourceContext([]);

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
            new RemoteSourceContext([
                'unsupportedParameter' => 'test',
            ]);
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals(
                'Context parameter unsupportedParameter is not supported.',
                $exception->getMessage()
            );
            ++$numberOfExceptions;
        }

        $sut = new RemoteSourceContext([]);

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
                new RemoteSourceContext([
                    $parameter => $invalidValue,
                ]);
            } catch (InvalidArgumentException $exception) {
                $this->assertEquals(
                    sprintf('Context parameter %s is not valid.', $parameter),
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
        $sut = new RemoteSourceContext([]);

        foreach ($invalidValues as $invalidValue) {
            try {
                $sut->setParameter($parameter, $invalidValue);
            } catch (InvalidArgumentException $exception) {
                $this->assertEquals(
                    sprintf('Context parameter %s is not valid.', $parameter),
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
            RemoteSourceContext::PARAM_SOURCE_URL => 'Source URL',
            RemoteSourceContext::PARAM_URI_PATH => 'URI path',
            RemoteSourceContext::PARAM_LABEL_PATH => 'Label path',
            RemoteSourceContext::PARAM_DEPENDENCY_URI_PATH => 'Dependency URI path',
            RemoteSourceContext::PARAM_PARSER => 'Parser',
            RemoteSourceContext::PARAM_JSON => ['json'],
        ];

        $sut = new RemoteSourceContext($parametersValues);

        foreach ($parametersValues as $parameter => $value) {
            $this->assertEquals($value, $sut->getParameter($parameter));
        }
    }

    /**
     * @dataProvider validValues
     */
    public function testSetAndGetValues(string $parameter, array $validValues): void
    {
        $sut = new RemoteSourceContext([]);

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
        $invalidValuesForStringParameters = [
            $class,
            [],
            ['json'],
            123,
            null,
        ];

        return [
            'JSON' => [
                'parameter' => RemoteSourceContext::PARAM_JSON,
                'invalidValues' => [
                    $class,
                    'string',
                    123
                ],
            ],
            'Dependency URI path' => [
                'parameter' => RemoteSourceContext::PARAM_DEPENDENCY_URI_PATH,
                'values' => [
                    $class,
                    [],
                    ['json'],
                    123
                ],
            ],
            'Source URL' => [
                'parameter' => RemoteSourceContext::PARAM_SOURCE_URL,
                'values' => $invalidValuesForStringParameters,
            ],
            'URI path' => [
                'parameter' => RemoteSourceContext::PARAM_URI_PATH,
                'values' => $invalidValuesForStringParameters,
            ],
            'Label path' => [
                'parameter' => RemoteSourceContext::PARAM_LABEL_PATH,
                'values' => $invalidValuesForStringParameters,
            ],
            'Parser' => [
                'parameter' => RemoteSourceContext::PARAM_PARSER,
                'values' => $invalidValuesForStringParameters,
            ],
        ];
    }

    public function validValues(): array
    {
        return [
            'JSON' => [
                'parameter' => RemoteSourceContext::PARAM_JSON,
                'values' => [
                    [],
                    ['json'],
                ],
            ],
            'Dependency URI path' => [
                'parameter' => RemoteSourceContext::PARAM_DEPENDENCY_URI_PATH,
                'values' => [
                    null,
                    'string',
                ],
            ],
            'Source URL' => [
                'parameter' => RemoteSourceContext::PARAM_SOURCE_URL,
                'values' => ['string'],
            ],
            'URI path' => [
                'parameter' => RemoteSourceContext::PARAM_URI_PATH,
                'values' => ['string'],
            ],
            'Label path' => [
                'parameter' => RemoteSourceContext::PARAM_LABEL_PATH,
                'values' => ['string'],
            ],
            'Parser' => [
                'parameter' => RemoteSourceContext::PARAM_PARSER,
                'values' => ['string'],
            ],
        ];
    }
}
