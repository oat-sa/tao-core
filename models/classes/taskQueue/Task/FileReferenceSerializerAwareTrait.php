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

use oat\generis\model\fileReference\FileReferenceSerializer;
use oat\oatbox\filesystem\File;

trait FileReferenceSerializerAwareTrait
{
    /**
     * @return FileReferenceSerializer
     */
    abstract protected function getFileReferenceSerializer();

    /**
     * Checks if it's a serialized file reference.
     *
     * @param string $serial
     * @return bool
     */
    protected function isFileReferenced($serial)
    {
        return is_object($this->getReferencedFile($serial));
    }

    /**
     * Tries to get the referenced file if it exists.
     *
     * @param string $serial
     * @return null|File
     */
    protected function getReferencedFile($serial)
    {
        try {
            $file = $this->getFileReferenceSerializer()->unserialize($serial);
            if ($file instanceof File && $file->exists()) {
                return $file;
            }
        } catch (\Exception $e) {
            // do nothing
        }

        return null;
    }
}