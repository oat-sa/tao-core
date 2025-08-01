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
use oat\tao\model\IdentifierGenerator\Repository\UniqueIdRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UniqueIdRepositoryTest extends TestCase
{
    private UniqueIdRepository $repository;
    private MockObject $persistenceManager;

    protected function setUp(): void
    {
        $this->persistenceManager = $this->createMock(PersistenceManager::class);
        $this->repository = new UniqueIdRepository($this->persistenceManager);
    }

    public function testGetStartIdDefaultValue(): void
    {
        unset($_ENV['TAO_ID_GENERATOR_ID_START']);

        $result = $this->repository->getStartId();

        $this->assertEquals(100000000, $result);
    }

    public function testGetStartIdWithDifferentValues(): void
    {
        $testValues = [
            '0' => 0,
            '150000000' => 150000000,
            '500000000' => 500000000,
            '999999999' => 999999999,
        ];

        foreach ($testValues as $envValue => $expectedResult) {
            $_ENV['TAO_ID_GENERATOR_ID_START'] = $envValue;

            $result = $this->repository->getStartId();

            $this->assertEquals($expectedResult, $result);

            unset($_ENV['TAO_ID_GENERATOR_ID_START']);
        }
    }

    public function testConstructorWithDefaultPersistenceId(): void
    {
        $repository = new UniqueIdRepository($this->persistenceManager);

        $this->assertInstanceOf(UniqueIdRepository::class, $repository);
    }

    public function testConstructorWithCustomPersistenceId(): void
    {
        $customPersistenceId = 'custom-persistence';

        $repository = new UniqueIdRepository($this->persistenceManager, $customPersistenceId);

        $this->assertInstanceOf(UniqueIdRepository::class, $repository);
    }
}
