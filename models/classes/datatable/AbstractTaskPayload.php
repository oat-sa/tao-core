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

namespace oat\tao\model\datatable;

use oat\oatbox\task\Task;

/**
 * Class AbstractTaskPayload
 * It will return data to represent a Task
 *
 * @package oat\oatbox\task
 * @author Antoine Robin, <antoine@taotesting.com>
 */
abstract class AbstractTaskPayload extends AbstractPayload
{


    /**
     * Search task with specific filters
     * @return Task[]
     */
    abstract protected function search();

    /**
     * Return the payload to display to user
     * @return array
     */
    protected function getData()
    {

        $iterator = $this->search();

        $taskList = [];

        foreach ($iterator as $task) {
            $taskList[] =
                [
                    "id" => $task->getId(),
                    "label" => $task->getLabel(),
                    "added" => $task->getCreationDate(),
                    "status" => $task->getStatus(),
                    "report" => json_decode($task->getReport(), true),
                ];
        }

        return $taskList;
    }



}
