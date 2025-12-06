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
 * Copyright (c) 2021-2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\task\migration\service;

use PHPUnit\Framework\MockObject\MockObject;
use oat\generis\test\OntologyMockTrait;
use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\tao\model\task\migration\MigrationConfig;
use oat\tao\model\task\migration\service\ResultFilter;
use oat\tao\model\task\migration\service\ResultFilterFactory;
use oat\tao\model\task\migration\service\StatementLastIdRetriever;

class ResultFilterFactoryTest extends TestCase
{
    use OntologyMockTrait;
    use ServiceManagerMockTrait;

    private ResultFilterFactory $subject;
    private StatementLastIdRetriever|MockObject $statementLastIdRetriever;

    protected function setUp(): void
    {
        $this->subject = new ResultFilterFactory();
        $this->statementLastIdRetriever = $this->createMock(StatementLastIdRetriever::class);

        $this->subject->setServiceLocator(
            $this->getServiceManagerMock(
                [
                    StatementLastIdRetriever::class => $this->statementLastIdRetriever
                ]
            )
        );
    }

    public function testCreate(): void
    {
        $this->statementLastIdRetriever
            ->method('retrieve')
            ->willReturn(10);

        $config = new MigrationConfig(
            [
                'start' => 5,
            ],
            5,
            1,
            true
        );

        $this->assertEquals(
            new ResultFilter(
                [
                    'start' => 5,
                    'end' => 10,
                    'max' => 10
                ]
            ),
            $this->subject->create($config)
        );
    }
}
