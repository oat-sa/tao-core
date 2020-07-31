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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\task\migration\service;

use common_persistence_AdvKeyValuePersistence;
use core_kernel_persistence_Exception;
use core_kernel_persistence_smoothsql_SmoothModel;
use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\taoMediaManager\model\relation\service\MediaToMediaRdsSearcher;

class MediaToMediaRdsSearcherTest extends TestCase
{
    /**
     * @var MediaToMediaRdsSearcher
     */
    private $subject;


    /**
     * @var core_kernel_persistence_smoothsql_SmoothModel|MockObject
     */
    private $ontologyMock;

    public function setUp(): void
    {
        $this->ontologyMock = $this->createMock(core_kernel_persistence_smoothsql_SmoothModel::class);

        $this->subject = new MediaToMediaRdsSearcher();
        $this->subject->setModel($this->ontologyMock);
    }

    public function testSearchWrongPersistence(): void
    {
        $advKeyValuePersistence = $this->createMock(common_persistence_AdvKeyValuePersistence::class);
        $this->ontologyMock->method('getPersistence')->willReturn($advKeyValuePersistence);

        $this->expectException(core_kernel_persistence_Exception::class);
        $this->subject->search(0, 2, 1);
    }
}