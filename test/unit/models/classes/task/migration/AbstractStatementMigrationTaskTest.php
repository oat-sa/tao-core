<?php declare(strict_types=1);

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


namespace oat\tao\test\unit\models\classes\task\migration;


use common_exception_MissingParameter;
use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\tao\model\task\migration\AbstractStatementMigrationTask;

class AbstractStatementMigrationTaskTest extends TestCase
{
    /**
     * @var AbstractStatementMigrationTask|MockObject
     */
    private $subject;

    public function setUp(): void
    {
        $this->subject = $this->getMockForAbstractClass(AbstractStatementMigrationTask::class);
    }

    public function testInvokeWithNoParams(): void
    {
        $this->expectException(common_exception_MissingParameter::class);
        $this->subject->__invoke([]);
    }
}