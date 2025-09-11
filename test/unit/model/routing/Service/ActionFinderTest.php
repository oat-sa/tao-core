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
 * Copyright (c) 2021  (original work) Open Assessment Technologies SA;
 */

namespace oat\test\model\routing\Service;

use oat\tao\model\routing\Contract\ActionInterface;
use oat\tao\model\routing\Service\ActionFinder;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use stdClass;
use PHPUnit\Framework\TestCase;

class DummyDependencyClass
{
}

class DummyActionControllerClass implements ActionInterface
{
    /** @var DummyDependencyClass */
    private $dummyDependencyClass;

    public function __construct(DummyDependencyClass $DummyDependencyClass)
    {
        $this->dummyDependencyClass = $DummyDependencyClass;
    }

    public function getDummyDependencyClass(): DummyDependencyClass
    {
        return $this->dummyDependencyClass;
    }
}

class ActionFinderTest extends TestCase
{
    /** @var ActionFinder */
    private $sut;

    /** @var MockObject|ContainerInterface */
    private $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->createMock(ContainerInterface::class);
        $this->sut = new ActionFinder($this->container, $this->createMock(LoggerInterface::class));
    }

    public function testFindFromContainerWillReturnObject(): void
    {
        $this->container
            ->method('has')
            ->with(self::class)
            ->willReturn(true);

        $this->container
            ->method('get')
            ->with(self::class)
            ->willReturn($this);

        $this->assertSame($this, $this->sut->find(self::class));
    }

    public function testFindNotAutowiredWillReturnNull(): void
    {
        $this->container
            ->method('has')
            ->willReturn(false);

        $this->assertNull($this->sut->find(StdClass::class));
    }

    public function testFindNotLegacyControllerWillReturnObject(): void
    {
        $dummyDependencyClass = new DummyDependencyClass();

        $this->container
            ->method('has')
            ->willReturnCallback(
                static function (string $class) use ($dummyDependencyClass): bool {
                    return $class === DummyDependencyClass::class;
                }
            );

        $this->container
            ->method('get')
            ->with(DummyDependencyClass::class)
            ->willReturn($dummyDependencyClass);

        /** @var DummyActionControllerClass $actionControllerClass */
        $actionControllerClass = $this->sut->find(DummyActionControllerClass::class);

        $this->assertInstanceOf(DummyActionControllerClass::class, $actionControllerClass);
        $this->assertSame($dummyDependencyClass, $actionControllerClass->getDummyDependencyClass());
    }
}
