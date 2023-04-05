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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\resources\Service;

use core_kernel_classes_Resource;
use InvalidArgumentException;
use oat\generis\model\data\Ontology;
use oat\tao\model\resources\Command\ResourceTransferCommand;
use oat\tao\model\resources\Contract\ResourceTransferInterface;
use oat\tao\model\resources\Service\ResourceTransferProxy;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ResourceTransferProxyTest extends TestCase
{
    private ResourceTransferProxy $sut;

    /** @var ResourceTransferInterface|MockObject */
    private ResourceTransferInterface $classCopier;

    /** @var ResourceTransferInterface|MockObject */
    private ResourceTransferInterface $instanceCopier;

    /** @var ResourceTransferInterface|MockObject */
    private ResourceTransferInterface $classMover;

    /** @var ResourceTransferInterface|MockObject */
    private ResourceTransferInterface $instanceMover;

    /** @var Ontology|MockObject */
    private $ontology;

    protected function setUp(): void
    {
        $this->classCopier = $this->createMock(ResourceTransferInterface::class);
        $this->instanceCopier = $this->createMock(ResourceTransferInterface::class);
        $this->classMover = $this->createMock(ResourceTransferInterface::class);
        $this->instanceMover = $this->createMock(ResourceTransferInterface::class);
        $this->ontology = $this->createMock(Ontology::class);

        $this->sut = new ResourceTransferProxy(
            $this->classCopier,
            $this->instanceCopier,
            $this->classMover,
            $this->instanceMover,
            $this->ontology
        );
    }

    /**
     * @dataProvider transferDataProvider
     */
    public function testTransfer(
        string $copierAttribute,
        bool $isFromAClass,
        bool $isToAClass,
        string $mode
    ): void {
        $this->mockFromToResource($isFromAClass, $isToAClass);

        $command = new ResourceTransferCommand(
            'fromResourceUri',
            'toResourceUri',
            ResourceTransferCommand::ACL_KEEP_ORIGINAL,
            $mode
        );

        /** @var ResourceTransferInterface|MockObject $copier */
        $copier = $this->{$copierAttribute};
        $copier->expects($this->once())
            ->method('transfer')
            ->with($command);

        $this->sut->transfer($command);
    }

    public function testTransferWithInvalidDestination(): void
    {
        $this->mockFromToResource(true, false);

        $command = new ResourceTransferCommand(
            'fromResourceUri',
            'toResourceUri',
            ResourceTransferCommand::ACL_KEEP_ORIGINAL,
            ResourceTransferCommand::TRANSFER_MODE_MOVE
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The destination resource [toResourceUri:] is not a class');

        $this->sut->transfer($command);
    }

    public function transferDataProvider(): array
    {
        return [
            'Use class copier if copy from/to class' => [
                'classCopier',
                true,
                true,
                ResourceTransferCommand::TRANSFER_MODE_COPY
            ],
            'Use instance copier if copy from resource to class' => [
                'instanceCopier',
                false,
                true,
                ResourceTransferCommand::TRANSFER_MODE_COPY
            ],
            'Use class mover if copy from/to class' => [
                'classMover',
                true,
                true,
                ResourceTransferCommand::TRANSFER_MODE_MOVE
            ],
            'Use instance mover if copy from instance to class' => [
                'instanceMover',
                false,
                true,
                ResourceTransferCommand::TRANSFER_MODE_MOVE
            ],
        ];
    }

    private function mockFromToResource(bool $isFromAClass, bool $isToAClass): void
    {
        $fromResource = $this->createMock(core_kernel_classes_Resource::class);
        $toResource = $this->createMock(core_kernel_classes_Resource::class);

        $fromResource->method('isClass')
            ->willReturn($isFromAClass);

        $toResource->method('isClass')
            ->willReturn($isToAClass);

        $this->ontology
            ->method('getResource')
            ->willReturnCallback(
                function ($uri) use ($fromResource, $toResource) {
                    if ($uri === 'fromResourceUri') {
                        return $fromResource;
                    }

                    if ($uri === 'toResourceUri') {
                        return $toResource;
                    }

                    return null;
                }
            );
    }
}
