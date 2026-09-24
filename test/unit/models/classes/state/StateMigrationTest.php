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
 * Foundation, Inc., 31 Milk St # 960789 Boston, MA 02196 USA
 *
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\state;

use oat\generis\test\ServiceManagerMockTrait;
use oat\oatbox\filesystem\FileSystem;
use oat\oatbox\filesystem\FileSystemService;
use oat\tao\model\state\StateMigration;
use oat\tao\model\state\StateStorage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class StateMigrationTest extends TestCase
{
    use ServiceManagerMockTrait;

    private const FILESYSTEM_ID = 'stateBackup';
    private const USER_ID = 'd250fc6f:S_XX99929000004:01925ab3-7497-478e-8f2f-626d267a3eea';
    private const CALL_ID = 'kve_de_http://example.org#i6a98';

    /** @var StateStorage|MockObject */
    private $stateStorage;

    /** @var FileSystem|MockObject */
    private $fileSystem;

    private StateMigration $subject;

    protected function setUp(): void
    {
        $this->stateStorage = $this->createMock(StateStorage::class);
        $this->fileSystem = $this->createMock(FileSystem::class);

        $fileSystemService = $this->createMock(FileSystemService::class);
        $fileSystemService
            ->method('getFileSystem')
            ->with(self::FILESYSTEM_ID)
            ->willReturn($this->fileSystem);

        $this->subject = new StateMigration([
            StateMigration::OPTION_FILESYSTEM => self::FILESYSTEM_ID,
        ]);
        $this->subject->setServiceLocator($this->getServiceManagerMock([
            StateStorage::SERVICE_ID => $this->stateStorage,
            FileSystemService::SERVICE_ID => $fileSystemService,
        ]));
    }

    public function testArchiveReturnsFalseWhenStateIsMissing(): void
    {
        $this->stateStorage
            ->expects($this->once())
            ->method('get')
            ->with(self::USER_ID, self::CALL_ID)
            ->willReturn(null);

        $this->fileSystem->expects($this->never())->method('write');

        $this->assertFalse($this->subject->archive(self::USER_ID, self::CALL_ID));
    }

    public function testArchiveReturnsTrueWhenWriteSucceedsWithVoidReturn(): void
    {
        $state = '{"item":"response"}';
        $serial = md5(self::USER_ID . self::CALL_ID);

        $this->stateStorage
            ->expects($this->once())
            ->method('get')
            ->with(self::USER_ID, self::CALL_ID)
            ->willReturn($state);

        // Flysystem 3 write(): void — must not be used as the archive() return value.
        $this->fileSystem
            ->expects($this->once())
            ->method('write')
            ->with($serial, $state);

        $this->assertTrue($this->subject->archive(self::USER_ID, self::CALL_ID));
    }

    public function testArchivePropagatesWriteFailure(): void
    {
        $this->stateStorage
            ->method('get')
            ->willReturn('{"item":"response"}');

        $this->fileSystem
            ->method('write')
            ->willThrowException(new RuntimeException('unable to write'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('unable to write');

        $this->subject->archive(self::USER_ID, self::CALL_ID);
    }

    public function testHasBackupDelegatesToFileExists(): void
    {
        $serial = md5(self::USER_ID . self::CALL_ID);

        $this->fileSystem
            ->expects($this->once())
            ->method('fileExists')
            ->with($serial)
            ->willReturn(true);

        $this->assertTrue($this->subject->hasBackup(self::USER_ID, self::CALL_ID));
    }

    public function testHasBackupReturnsFalseWhenMissing(): void
    {
        $this->fileSystem
            ->method('fileExists')
            ->willReturn(false);

        $this->assertFalse($this->subject->hasBackup(self::USER_ID, self::CALL_ID));
    }
}
