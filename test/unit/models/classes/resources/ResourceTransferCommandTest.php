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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\resources\ResourceService;

use oat\tao\model\resources\Command\ResourceTransferCommand;
use PHPUnit\Framework\TestCase;

class ResourceTransferCommandTest extends TestCase
{
    public function testGetters(): void
    {
        $command = new ResourceTransferCommand(
            'fromUri',
            'toUri',
            ResourceTransferCommand::ACL_KEEP_ORIGINAL,
            ResourceTransferCommand::TRANSFER_MODE_COPY
        );

        $this->assertSame('fromUri', $command->getFrom());
        $this->assertSame('toUri', $command->getTo());
        $this->assertTrue($command->isCopyTo());
        $this->assertFalse($command->isMoveTo());
        $this->assertTrue($command->keepOriginalAcl());
        $this->assertFalse($command->useDestinationAcl());
    }
}
