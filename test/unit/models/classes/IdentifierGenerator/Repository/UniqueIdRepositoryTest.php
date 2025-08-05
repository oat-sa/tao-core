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
 * Copyright (c) 2025 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\IdentifierGenerator\Repository;

use oat\generis\persistence\PersistenceManager;
use oat\oatbox\log\LoggerService;
use oat\tao\model\IdentifierGenerator\Repository\UniqueIdRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UniqueIdRepositoryTest extends TestCase
{
    private UniqueIdRepository $repository;
    private MockObject $persistenceManager;
    private MockObject $logger;

    protected function setUp(): void
    {
        $this->persistenceManager = $this->createMock(PersistenceManager::class);
        $this->logger = $this->createMock(LoggerService::class);

        $this->repository = new UniqueIdRepository($this->persistenceManager, $this->logger);
    }

    public function testConstructorWithDefaultPersistenceId(): void
    {
        $repository = new UniqueIdRepository($this->persistenceManager, $this->logger);

        $this->assertInstanceOf(UniqueIdRepository::class, $repository);
    }

    public function testConstructorWithCustomPersistenceId(): void
    {
        $customPersistenceId = 'custom-persistence';

        $repository = new UniqueIdRepository($this->persistenceManager, $this->logger, $customPersistenceId);

        $this->assertInstanceOf(UniqueIdRepository::class, $repository);
    }

    public function testTableNameConstant(): void
    {
        $this->assertEquals('unique_ids', UniqueIdRepository::TABLE_NAME);
    }
}
