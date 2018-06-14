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

namespace oat\tao\model\taskQueue\Task;

/**
 * Methods to define dependency between tasks.
 *
 * @author Gyula Szucs <gyula@taotesting.com>
 */
interface ChildTaskAwareInterface
{
    /**
     * Adds a new child task.
     *
     * @param string $taskId
     * @return ChildTaskAwareInterface
     */
    public function addChildId($taskId);

    /**
     * Is there any child task set?
     *
     * @return bool
     */
    public function hasChildren();

    /**
     * Returns the array of child tasks' ids.
     *
     * @return string[]
     */
    public function getChildren();
}