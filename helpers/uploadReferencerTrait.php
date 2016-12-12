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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\helpers;

use oat\generis\model\fileReference\UrlFileSerializer;
use oat\oatbox\filesystem\File;
use oat\oatbox\service\ServiceManager;

/**
 * Class uploadReferencerTrait
 * @package oat\tao\helpers
 */
trait uploadReferencerTrait
{
    private $uriSerializer;

    /**
     *
     * @return UrlFileSerializer
     */
    protected function getSerializer()
    {
        if (!$this->uriSerializer) {
            $this->uriSerializer = new UrlFileSerializer();
            $this->uriSerializer->setServiceLocator(ServiceManager::getServiceManager());
        }
        return $this->uriSerializer;
    }

    /**
     * Detects
     * @param $file
     * @return File
     * @throws \common_Exception
     */
    protected function universalizeUpload($file)
    {
        if (filter_var($file, FILTER_VALIDATE_URL)) {
            return $this->getSerializer()->unserializeFile($file);
        }
        if (is_file($file)) {
            return $file;
        }

        throw new \common_Exception('Unsupported file reference');
    }

    /**
     * Either create local copy or use original location for tile
     * Returns absolute path to the file, to be compatible with legacy methods
     * @param $serial
     * @return string
     */
    protected function getLocalCopy($serial)
    {
        $file = $this->universalizeUpload($serial);
        if ($file instanceof File) {
            $tmpName = \tao_helpers_File::concat([\tao_helpers_File::createTempDir(), $file->getPrefix()]);
            if (($resource = fopen($tmpName, 'wb')) !== false) {
                stream_copy_to_stream($file->readStream(), $resource);
                fclose($resource);
                return $tmpName;
            }
        }
        return $serial;
    }
}