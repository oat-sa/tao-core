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

namespace oat\tao\test\unit\models\classes\task\migration\service;

use PHPUnit\Framework\TestCase;
use oat\tao\model\task\migration\MigrationConfig;
use oat\tao\model\task\migration\service\ResultFilter;
use oat\tao\model\task\migration\service\SpawnMigrationConfigService;

class SpawnMigrationConfigServiceTest extends TestCase
{
    /** @var SpawnMigrationConfigService */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new SpawnMigrationConfigService();
    }

    public function testSpawnWillPaginateConfiguration(): void
    {
        $this->assertEquals(
            new MigrationConfig(
                [
                    'start' => 5,
                ],
                10,
                1,
                true
            ),
            $this->subject->spawn(
                new MigrationConfig(
                    [
                        'start' => 0,
                    ],
                    10,
                    1,
                    true
                ),
                new ResultFilter(
                    [
                        'end' => 5,
                        'max' => 10,
                    ]
                )
            )
        );
    }

    public function testSpawnWillReturnNullWhenReachLimit(): void
    {
        $this->assertNull(
            $this->subject->spawn(
                new MigrationConfig(
                    [
                        'start' => 5,
                    ],
                    10,
                    1,
                    true
                ),
                new ResultFilter(
                    [
                        'end' => 10,
                        'max' => 10,
                    ]
                )
            )
        );
    }
}
