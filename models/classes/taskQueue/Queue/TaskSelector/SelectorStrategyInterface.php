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

namespace oat\tao\model\taskQueue\Queue\TaskSelector;

use oat\oatbox\PhpSerializable;
use oat\tao\model\taskQueue\QueueInterface;
use oat\tao\model\taskQueue\Task\TaskInterface;

/**
 * @author Gyula Szucs <gyula@taotesting.com>
 */
interface SelectorStrategyInterface extends PhpSerializable
{
    /**
     * @param QueueInterface[] $queues
     * @return null|TaskInterface
     */
    public function pickNextTask(array $queues);

    /**
     * A time in seconds for the worker to wait if there is no task.
     *
     * @return int
     */
    public function getWaitTime();
}