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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\IdentifierGenerator\Generator;

use core_kernel_classes_Resource;
use InvalidArgumentException;
use oat\generis\model\data\Ontology;
use oat\tao\model\IdentifierGenerator\Generator\IdentifierGeneratorInterface;
use oat\tao\model\IdentifierGenerator\Generator\IdentifierGeneratorProxy;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IdentifierGeneratorProxyTest extends TestCase
{
    /** @var Ontology|MockObject */
    private Ontology $ontology;

    private IdentifierGeneratorProxy $sut;

    protected function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->sut = new IdentifierGeneratorProxy($this->ontology);
    }

    public function testNoResourceOrResourceIdProvided(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Option "%s" or "%s" is required to generate ID',
                IdentifierGeneratorInterface::OPTION_RESOURCE,
                IdentifierGeneratorInterface::OPTION_RESOURCE_ID
            )
        );

        $this->sut->generate();
    }

    public function testInvalidResourceOptionProvided(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Option "%s" must be an instance of %s',
                IdentifierGeneratorInterface::OPTION_RESOURCE,
                core_kernel_classes_Resource::class
            )
        );

        $this->sut->generate(['resource' => 'invalidResource']);
    }

    public function testMissedResourceType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ID generator for resource type missedResourceType not defined');

        $resource = $this->createMock(core_kernel_classes_Resource::class);
        $resource
            ->expects($this->once())
            ->method('getRootId')
            ->willReturn('missedResourceType');

        $this->sut->generate([IdentifierGeneratorInterface::OPTION_RESOURCE => $resource]);
    }

    public function testSuccess(): void
    {
        $resource = $this->createMock(core_kernel_classes_Resource::class);

        $options = [IdentifierGeneratorInterface::OPTION_RESOURCE => $resource];

        $resource
            ->expects($this->once())
            ->method('getRootId')
            ->willReturn('resourceType');

        $generator = $this->createMock(IdentifierGeneratorInterface::class);

        $generator
            ->expects($this->once())
            ->method('generate')
            ->with($options)
            ->willReturn('identifier');

        $this->sut->addIdentifierGenerator($generator, 'resourceType');

        $this->assertEquals('identifier', $this->sut->generate($options));
    }
}
