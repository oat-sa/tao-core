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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 *
 * @author Andrei Shapiro <andrei.shapiro@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\resources\Service;

use PHPUnit\Framework\TestCase;
use oat\oatbox\filesystem\FileSystemService;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\resources\Service\InstanceMetadataCopier;
use oat\generis\model\fileReference\FileReferenceSerializer;
use oat\tao\model\resources\Contract\ClassMetadataMapperInterface;

class InstanceMetadataCopierTest extends TestCase
{
    /** @var InstanceMetadataCopier */
    private $sut;

    /** @var ClassMetadataMapperInterface|MockObject */
    private $classMetadataMapper;

    /** @var FileReferenceSerializer|MockObject */
    private $fileReferenceSerializer;

    /** @var FileSystemService|MockObject */
    private $fileSystemService;

    protected function setUp(): void
    {
        $this->classMetadataMapper = $this->createMock(ClassMetadataMapperInterface::class);
        $this->fileReferenceSerializer = $this->createMock(FileReferenceSerializer::class);
        $this->fileSystemService = $this->createMock(FileSystemService::class);

        $this->sut = new InstanceMetadataCopier(
            $this->classMetadataMapper,
            $this->fileReferenceSerializer,
            $this->fileSystemService
        );
    }

    public function testCopy(): void
    {
        $this->markTestIncomplete();
    }
}
