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

class TaskFactory
{
    /**
     * @param array $basicData
     *
     * @return TaskInterface
     * @throws InvalidTaskException
     */
    public static function build($basicData)
    {
        $className = $basicData[TaskInterface::JSON_TASK_CLASS_NAME_KEY];

        if (class_exists($className) && is_subclass_of($className, TaskInterface::class)) {
            $metaData = $basicData[TaskInterface::JSON_METADATA_KEY];

            /** @var TaskInterface $task */
            $task = new $className($metaData[TaskInterface::JSON_METADATA_ID_KEY], $metaData[TaskInterface::JSON_METADATA_OWNER_KEY]);
            $task->setMetadata($metaData);
            $task->setParameter($basicData[TaskInterface::JSON_PARAMETERS_KEY]);

            if (isset($metaData[TaskInterface::JSON_METADATA_CREATED_AT_KEY])) {
                $task->setCreatedAt(new \DateTime($metaData[TaskInterface::JSON_METADATA_CREATED_AT_KEY]));
            }

            return $task;
        }

        throw new InvalidTaskException;
    }

}