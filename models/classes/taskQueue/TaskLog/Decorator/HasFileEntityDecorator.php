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

use oat\oatbox\filesystem\Directory;
use oat\oatbox\filesystem\FileSystemService;
use oat\tao\model\taskQueue\QueueDispatcherInterface;
use oat\tao\model\taskQueue\TaskLog\Entity\EntityInterface;

/**
 * @author Gyula Szucs <gyula@taotesting.com>
 */
class HasFileEntityDecorator extends TaskLogEntityDecorator
{
    /**
     * @var FileSystemService
     */
    private $fileSystemService;

    public function __construct(EntityInterface $entity, FileSystemService $fileSystemService)
    {
        parent::__construct($entity);

        $this->fileSystemService = $fileSystemService;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Add 'hasFile' to the result. Required by our frontend.
     *
     * @return array
     */
    public function toArray()
    {
        $result = parent::toArray();

        $result['hasFile'] = false;

        $fileName = $this->getFileNameFromReport();

        if ($fileName) {
            /** @var Directory $queueStorage */
            $queueStorage = $this->fileSystemService
                ->getDirectory(QueueDispatcherInterface::FILE_SYSTEM_ID);

            if ($queueStorage->getFile($fileName)->exists()) {
                $result['hasFile'] = true;
            }
        }

        return $result;
    }
}