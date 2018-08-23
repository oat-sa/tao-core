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

use oat\tao\model\taskQueue\TaskLog\CollectionInterface;
use oat\tao\model\taskQueue\TaskLog\Entity\EntityInterface;

/**
 * @author Gyula Szucs <gyula@taotesting.com>
 */
abstract class TaskLogCollectionDecorator implements CollectionInterface
{
    /**
     * @var CollectionInterface
     */
    private $collection;

    /**
     * @param CollectionInterface $collection
     */
    public function __construct(CollectionInterface $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @return \Traversable|EntityInterface[]
     */
    public function getIterator()
    {
        return $this->collection->getIterator();
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return $this->collection->count();
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        return $this->collection->toArray();
    }

    /**
     * @inheritdoc
     */
    public function isEmpty()
    {
        return $this->collection->isEmpty();
    }

    /**
     * @inheritdoc
     */
    public function first()
    {
        return $this->collection->first();
    }

    /**
     * @inheritdoc
     */
    public function last()
    {
        return $this->collection->last();
    }

    /**
     * @inheritdoc
     */
    public function getIds()
    {
        return $this->collection->getIds();
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return $this->collection->jsonSerialize();
    }
}