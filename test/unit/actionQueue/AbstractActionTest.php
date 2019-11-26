<?php

declare(strict_types=1);

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
 */

namespace oat\tao\test\unit\actionQueue;

use oat\generis\test\TestCase;
use oat\tao\model\actionQueue\AbstractQueuedAction;

/**
 * Class ActionTest
 * @package oat\tao\test\unit\datatable
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 */
class AbstractActionTest extends TestCase
{
    public function testGetId(): void
    {
        $action = new ConcreteAction();
        $this->assertSame(ConcreteAction::class, $action->getId());
    }

    public function testGetResult(): void
    {
        $action = new ConcreteAction();
        $action->setResult('result');
        $this->assertSame('result', $action->getResult());
    }

    public function testSetResult(): void
    {
        $action = new ConcreteAction();
        $action->setResult('result');
        $this->assertSame('result', $action->getResult());
    }

    /**
     * @expectedException \oat\tao\model\actionQueue\ActionQueueException
     */
    public function testGetResultException(): void
    {
        $action = new ConcreteAction();
        $action->getResult();
    }
}

class ConcreteAction extends AbstractQueuedAction
{
    public function __invoke($params)
    {
        return 'getmypid';
    }

    public function getNumberOfActiveActions()
    {
        return 10;
    }
}
