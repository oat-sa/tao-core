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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\test\unit\model\taskQueue\TaskLog;

use Exception;
use oat\tao\model\taskQueue\TaskLog\CategorizedStatus;
use PHPUnit\Framework\TestCase;

class CategorizedStatusTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testCreateWithValidStatus()
    {
        $status = CategorizedStatus::createFromString('enqueued');
        $this->assertInstanceOf(CategorizedStatus::class, $status);
    }

    /**
     * @throws Exception
     */
    public function testCreateWithInvalidStatus()
    {
        $this->expectException(Exception::class);
        CategorizedStatus::createFromString('some invalid status');
    }

    /**
     * @throws Exception
     */
    public function testStatusAreMappedCorrectly()
    {
        $status = CategorizedStatus::createFromString('enqueued');
        $this->assertSame('created', (string)$status);

        $status = CategorizedStatus::createFromString('dequeued');
        $this->assertSame('in_progress', (string)$status);

        $status = CategorizedStatus::createFromString('running');
        $this->assertSame('in_progress', (string)$status);

        $status = CategorizedStatus::createFromString('completed');
        $this->assertSame('completed', (string)$status);

        $status = CategorizedStatus::createFromString('failed');
        $this->assertSame('failed', (string)$status);

        $status = CategorizedStatus::createFromString('archived');
        $this->assertSame('archived', (string)$status);

        $status = CategorizedStatus::createFromString('cancelled');
        $this->assertSame('cancelled', (string)$status);

        $status = CategorizedStatus::createFromString('unknown');
        $this->assertSame('failed', (string)$status);
    }

    /**
     * @throws Exception
     */
    public function testStatusEquals()
    {
        $statusRunning = CategorizedStatus::createFromString('dequeued');
        $this->assertTrue($statusRunning->equals(CategorizedStatus::createFromString('dequeued')));

        $statusCompleted = CategorizedStatus::createFromString('completed');
        $this->assertTrue($statusCompleted->equals(CategorizedStatus::createFromString('completed')));

        $statusArchived = CategorizedStatus::createFromString('archived');
        $this->assertTrue($statusArchived->equals(CategorizedStatus::createFromString('archived')));

        $statusCancelled = CategorizedStatus::createFromString('cancelled');
        $this->assertTrue($statusCancelled->equals(CategorizedStatus::createFromString('cancelled')));

        $statusFailed = CategorizedStatus::createFromString('failed');
        $this->assertTrue($statusFailed->equals(CategorizedStatus::createFromString('unknown')));

        $this->assertFalse($statusRunning->equals($statusCompleted));
        $this->assertFalse($statusCompleted->equals($statusFailed));
    }

    /**
     * @throws Exception
     */
    public function testIsArchived()
    {
        $status = CategorizedStatus::createFromString('archived');
        $this->assertTrue($status->isArchived());
    }

    /**
     * @throws Exception
     */
    public function testIsCancelled()
    {
        $status = CategorizedStatus::createFromString('cancelled');
        $this->assertTrue($status->isCancelled());
    }
}
