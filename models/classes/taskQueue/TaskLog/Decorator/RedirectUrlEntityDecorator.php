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

namespace oat\tao\model\taskQueue\TaskLog\Decorator;

use oat\tao\model\taskQueue\TaskLog\Entity\EntityInterface;

/**
 * @author Gyula Szucs <gyula@taotesting.com>
 */
class RedirectUrlEntityDecorator extends TaskLogEntityDecorator
{
    public function __construct(EntityInterface $entity)
    {
        parent::__construct($entity);
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Add 'redirectUrl' to the result if the task has been processed.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getStatus()->isCompleted() || $this->getStatus()->isArchived()
            ? array_merge(parent::toArray(), [
                'redirectUrl' => _url('redirectToInstance', 'TaskQueueWebApi', 'tao', ['taskId' => $this->getId()])
              ])
            : parent::toArray();
    }
}