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

namespace oat\tao\model\taskQueue\Event;

use oat\oatbox\event\Event;
use oat\tao\model\taskQueue\TaskLog\Entity\EntityInterface;

class TaskLogArchivedEvent implements Event
{
    /**
     * @var EntityInterface
     */
    private $taskLogEntity;

    /**
     * @var bool
     */
    private $isForced;

    /**
     * TaskLogArchivedEvent constructor.
     *
     * @param EntityInterface $taskLogEntity
     * @param bool                   $isForced
     */
    public function __construct(EntityInterface $taskLogEntity, $isForced = false)
    {
        $this->taskLogEntity = $taskLogEntity;
        $this->isForced = $isForced;
    }

    /**
     * @return EntityInterface
     */
    public function getTaskLogEntity()
    {
        return $this->taskLogEntity;
    }

    /**
     * @return bool
     */
    public function isForced()
    {
        return $this->isForced;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return __CLASS__;
    }
}