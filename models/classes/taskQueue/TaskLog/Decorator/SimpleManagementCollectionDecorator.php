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

use oat\generis\model\fileReference\FileReferenceSerializer;
use oat\oatbox\filesystem\FileSystemService;
use oat\tao\model\taskQueue\TaskLog\CollectionInterface;
use oat\tao\model\taskQueue\TaskLogInterface;

/**
 * Containing all necessary modification required by the simple UI component.
 *
 * @author Gyula Szucs <gyula@taotesting.com>
 */
class SimpleManagementCollectionDecorator extends TaskLogCollectionDecorator
{
    /**
     * @var CollectionInterface
     */
    private $collection;

    /**
     * @var TaskLogInterface
     */
    private $taskLogService;

    /**
     * @var FileSystemService
     */
    private $fileSystemService;

    /**
     * @var bool
     */
    private $reportIncluded;

    /**
     * @var FileReferenceSerializer
     */
    private $fileReferenceSerializer;

    public function __construct(CollectionInterface $collection, TaskLogInterface $taskLogService, FileSystemService $fileSystemService, FileReferenceSerializer $fileReferenceSerializer, $reportIncluded)
    {
        parent::__construct($collection);

        $this->fileSystemService = $fileSystemService;
        $this->collection = $collection;
        $this->taskLogService = $taskLogService;
        $this->reportIncluded = (bool) $reportIncluded;
        $this->fileReferenceSerializer = $fileReferenceSerializer;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = [];

        foreach ($this->getIterator() as $entity) {
            $entityData = (
                new RedirectUrlEntityDecorator(
                    new HasFileEntityDecorator(
                        new CategoryEntityDecorator($entity, $this->taskLogService),
                        $this->fileSystemService,
                        $this->fileReferenceSerializer
                    ),
                    $this->taskLogService,
                    \common_session_SessionManager::getSession()->getUser()
                )
            )->toArray();

            if (!$this->reportIncluded && array_key_exists('report', $entityData)) {
                unset($entityData['report']);
            }

            $data[] = $entityData;
        }

        return $data;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
