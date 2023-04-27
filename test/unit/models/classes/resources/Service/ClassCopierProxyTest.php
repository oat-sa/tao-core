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
 * Copyright (c) 2022-2023 (original work) Open Assessment Technologies SA.
 *
 * @author Andrei Shapiro <andrei.shapiro@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\resources\Service;

use oat\generis\model\data\Ontology;
use oat\tao\model\resources\Command\ResourceTransferCommand;
use oat\tao\model\resources\Contract\ResourceTransferInterface;
use InvalidArgumentException;
use core_kernel_classes_Class;
use oat\tao\model\resources\ResourceTransferResult;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\resources\Service\ClassCopierProxy;
use oat\tao\model\resources\Contract\RootClassesListServiceInterface;

class ClassCopierProxyTest extends TestCase
{
    private ClassCopierProxy $sut;

    /** @var core_kernel_classes_Class|MockObject */
    private $rootClass;

    /** @var RootClassesListServiceInterface|MockObject */
    private $rootClassesListService;

    /** @var ResourceTransferInterface|MockObject */
    private $classCopier;

    /** @var Ontology|MockObject */
    private $ontology;

    protected function setUp(): void
    {
        $this->rootClass = $this->createMock(core_kernel_classes_Class::class);
        $this->rootClassesListService = $this->createMock(RootClassesListServiceInterface::class);
        $this->classCopier = $this->createMock(ResourceTransferInterface::class);
        $this->ontology = $this->createMock(Ontology::class);

        $this->sut = new ClassCopierProxy($this->rootClassesListService, $this->ontology);
    }

    public function testAddClassCopier(): void
    {
        $rootClassUri = 'rootClassUri';

        $this->rootClassesListService
            ->expects($this->once())
            ->method('listUris')
            ->willReturn([$rootClassUri]);

        $this->sut->addClassCopier($rootClassUri, $this->classCopier);
    }

    public function testAddClassCopierInvalidRootClassUri(): void
    {
        $this->rootClassesListService
            ->expects($this->once())
            ->method('listUris')
            ->willReturn(['rootClassUri']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Provided root class URI was not found in root classes list.');

        $this->sut->addClassCopier('invalidRootClassUri', $this->classCopier);
    }

    public function testAddClassCopierAlreadyConfigured(): void
    {
        $rootClassUri = 'rootClassUri';

        $this->rootClassesListService
            ->expects($this->exactly(2))
            ->method('listUris')
            ->willReturn([$rootClassUri]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Root class (%s) already configured to use copier service (%s)',
                $rootClassUri,
                get_class($this->classCopier)
            )
        );

        $this->sut->addClassCopier($rootClassUri, $this->classCopier);
        $this->sut->addClassCopier($rootClassUri, $this->classCopier);
    }

    public function testTransfer(): void
    {
        $this->doCopy(
            $this->createClass('fromClassUri'),
            $this->createClass('destinationClassUri'),
            $this->createClass('newClassUri')
        );

        $this->assertEquals(
            new ResourceTransferResult('newClassUri'),
            $this->sut->transfer(
                new ResourceTransferCommand(
                    'fromClassUri',
                    'destinationClassUri',
                    ResourceTransferCommand::ACL_KEEP_ORIGINAL,
                    ResourceTransferCommand::TRANSFER_MODE_COPY
                )
            )
        );
    }

    public function testCopy(): void
    {
        $class = $this->createClass('fromClassUri');
        $destinationClass = $this->createClass('destinationClassUri');
        $newClass = $this->createClass('newClassUri');

        $this->doCopy($class, $destinationClass, $newClass);

        $this->assertEquals($newClass, $this->sut->copy($class, $destinationClass));
    }

    public function testCopyInvalidClass(): void
    {
        $this->rootClass
            ->expects($this->never())
            ->method('getUri');

        $this->rootClassesListService
            ->expects($this->once())
            ->method('list')
            ->willReturn([$this->rootClass]);

        $class = $this->createMock(core_kernel_classes_Class::class);

        $class->expects($this->once())
            ->method('getUri')
            ->willReturn('classUri');

        $class->expects($this->once())
            ->method('equals')
            ->with($this->rootClass)
            ->willReturn(false);

        $class->expects($this->once())
            ->method('isSubClassOf')
            ->with($this->rootClass)
            ->willReturn(false);

        $this->ontology
            ->method('getClass')
            ->willReturn($class);

        $destinationClass = $this->createMock(core_kernel_classes_Class::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Provided class does not belong to any root class');

        $this->sut->copy($class, $destinationClass);
    }

    private function doCopy(MockObject $class, MockObject $destinationClass, MockObject $newClass): void
    {
        $rootClassUri = 'rootClassUri';

        $this->rootClass
            ->expects($this->once())
            ->method('getUri')
            ->willReturn($rootClassUri);

        $this->rootClassesListService
            ->expects($this->once())
            ->method('list')
            ->willReturn([$this->rootClass]);

        $this->rootClassesListService
            ->expects($this->once())
            ->method('listUris')
            ->willReturn([$rootClassUri]);

        $class->expects($this->once())
            ->method('equals')
            ->with($this->rootClass)
            ->willReturn(true);

        $class->expects($this->never())
            ->method('isSubClassOf')
            ->with($this->rootClass);

        $this->classCopier
            ->expects($this->once())
            ->method('transfer')
            ->with(
                new ResourceTransferCommand(
                    'fromClassUri',
                    'destinationClassUri',
                    ResourceTransferCommand::ACL_KEEP_ORIGINAL,
                    ResourceTransferCommand::TRANSFER_MODE_COPY
                )
            )
            ->willReturn(new ResourceTransferResult('newClassUri'));

        $this->ontology
            ->method('getClass')
            ->willReturnCallback(
                function ($uri) use ($class, $newClass) {
                    if ($uri === 'newClassUri') {
                        return $newClass;
                    }

                    if ($uri === 'fromClassUri') {
                        return $class;
                    }

                    return null;
                }
            );

        $this->sut->addClassCopier($rootClassUri, $this->classCopier);
    }

    /**
     * @return MockObject|core_kernel_classes_Class
     */
    private function createClass(string $uri): MockObject
    {
        $class = $this->createMock(core_kernel_classes_Class::class);

        $class->method('getUri')
            ->willReturn($uri);

        return $class;
    }
}
