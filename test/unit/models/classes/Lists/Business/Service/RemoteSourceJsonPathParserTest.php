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
 * Copyright (c) 2020-2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\Lists\Business\Service;

use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\Lists\Business\Domain\Value;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\Lists\Business\Domain\RemoteSourceContext;
use oat\tao\model\Lists\Business\Service\RemoteSourceJsonPathParser;

class RemoteSourceJsonPathParserTest extends TestCase
{
    use ServiceManagerMockTrait;

    private RemoteSourceJsonPathParser $sut;

    protected function setUp(): void
    {
        /** @var FeatureFlagCheckerInterface|MockObject $featureFlagChecker */
        $featureFlagChecker = $this->createMock(FeatureFlagCheckerInterface::class);
        $featureFlagChecker
            ->method('isEnabled')
            ->willReturn(true);

        $this->sut = new RemoteSourceJsonPathParser();
        $this->sut->setServiceLocator(
            $this->getServiceManagerMock([
                FeatureFlagChecker::class => $featureFlagChecker,
            ])
        );
    }

    public function testIterateWithoutDependencyUri(): void
    {
        $json = [
            'phoneNumbers' => [
                [
                    'type' => 'iPhone',
                    'number' => '0123-4567-8888',
                ],
                [
                    'type' => 'home',
                    'number' => '0123-4567-8910',
                ],
            ],
        ];

        $values = iterator_to_array(
            $this->sut->iterateByContext(
                $this->getRemoteSourceContextMock(
                    $json,
                    '$.phoneNumbers[*].type',
                    '$.phoneNumbers[*].number'
                )
            )
        );

        $this->assertEquals(new Value(null, 'iPhone', '0123-4567-8888'), $values[0]);
        $this->assertEquals(new Value(null, 'home', '0123-4567-8910'), $values[1]);
    }

    public function testIterateWithDependencyUri(): void
    {
        $json = [
            'phoneNumbers' => [
                [
                    'type' => 'iPhone',
                    'number' => '0123-4567-8888',
                    'country' => 'Luxembourg',
                ],
                [
                    'type' => 'home',
                    'number' => '0123-4567-8910',
                    'country' => 'Norway',
                ],
            ],
        ];

        $values = iterator_to_array(
            $this->sut->iterateByContext(
                $this->getRemoteSourceContextMock(
                    $json,
                    '$.phoneNumbers[*].type',
                    '$.phoneNumbers[*].number',
                    '$.phoneNumbers[*].country'
                )
            )
        );

        $value = new Value(null, 'iPhone', '0123-4567-8888', 'Luxembourg');
        $this->assertEquals($value, $values[0]);

        $value = new Value(null, 'home', '0123-4567-8910', 'Norway');
        $this->assertEquals($value, $values[1]);
    }

    private function getRemoteSourceContextMock(
        array $json,
        string $uriPath,
        string $labelPath,
        string $dependencyUriPath = null
    ): RemoteSourceContext {
        $remoteSourceContextMock = $this->createMock(RemoteSourceContext::class);

        $remoteSourceContextMock
            ->method('getParameter')
            ->willReturnCallback(
                function (string $parameter) use ($json, $uriPath, $labelPath, $dependencyUriPath) {
                    if ($parameter === RemoteSourceContext::PARAM_JSON) {
                        return $json;
                    }

                    if ($parameter === RemoteSourceContext::PARAM_URI_PATH) {
                        return $uriPath;
                    }

                    if ($parameter === RemoteSourceContext::PARAM_LABEL_PATH) {
                        return $labelPath;
                    }

                    if ($parameter === RemoteSourceContext::PARAM_DEPENDENCY_URI_PATH) {
                        return $dependencyUriPath;
                    }

                    return null;
                }
            );

        return $remoteSourceContextMock;
    }
}
