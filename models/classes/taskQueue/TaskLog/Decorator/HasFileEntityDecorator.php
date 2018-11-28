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
use oat\tao\model\taskQueue\Task\FileReferenceSerializerAwareTrait;
use oat\tao\model\taskQueue\Task\FilesystemAwareTrait;
use oat\tao\model\taskQueue\TaskLog\Entity\EntityInterface;

/**
 * @author Gyula Szucs <gyula@taotesting.com>
 */
class HasFileEntityDecorator extends TaskLogEntityDecorator
{
    use FilesystemAwareTrait;
    use FileReferenceSerializerAwareTrait;

    /**
     * @var FileSystemService
     */
    private $fileSystemService;

    /**
     * @var FileReferenceSerializer
     */
    private $fileReferenceSerializer;

    public function __construct(EntityInterface $entity, FileSystemService $fileSystemService, FileReferenceSerializer $fileReferenceSerializer)
    {
        parent::__construct($entity);

        $this->fileSystemService = $fileSystemService;
        $this->fileReferenceSerializer = $fileReferenceSerializer;
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

        $fileNameOrSerial = $this->getFileNameFromReport();

        if ($fileNameOrSerial) {
            $result['hasFile'] = $this->isFileReferenced($fileNameOrSerial)
                ? true
                : $this->isFileStoredInQueueStorage($fileNameOrSerial);
        }

        return $result;
    }

    protected function getFileSystemService()
    {
        return $this->fileSystemService;
    }

    protected function getFileReferenceSerializer()
    {
        return $this->fileReferenceSerializer;
    }
}