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
 * Copyright (c) 2022 Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\Observer\Log;

use PHPUnit\Framework\TestCase;
use oat\tao\model\Observer\Log\LoggerObserver;
use oat\tao\model\Observer\SubjectInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

class LoggerObserverTest extends TestCase
{
    /** @var LoggerInterface|MockObject */
    private $logger;

    /** @var LoggerObserver */
    private $subject;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->subject = new LoggerObserver($this->logger);
    }

    public function testUpdateWillLogInfo(): void
    {
        $subject = $this->createMock(SubjectInterface::class);

        $this->logger
            ->expects($this->once())
            ->method('info');

        $this->subject->update($subject);
    }
}
