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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA
 *
 */

namespace oat\tao\model\upload;

use common_Utils;
use oat\oatbox\AbstractRegistry;
use oat\oatbox\filesystem\File;

class TempFlyStorageAssociation extends AbstractRegistry implements TmpLocalAwareStorageInterface
{
    protected function getExtension()
    {
        return \common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
    }

    protected function getConfigId()
    {
        return 'tmp_fly_files_registry';
    }

    /**
     * @return TmpLocalAwareStorageInterface
     */
    public static function getStorage()
    {
        return self::getRegistry();
    }

    /**
     * @param $file
     */
    public function setUpload(File $file)
    {
        $hash = $this->getHashedKey($file);
        $this->set($hash, []);
    }

    /**
     * @param File $remote
     * @param string $local
     */
    public function addLocalCopies(File $remote, $local)
    {
        $hash = $this->getHashedKey($remote);
        $current = $this->getLocalCopies($remote);
        $current[] = $local;
        $this->set($hash, $current);
    }

    /**
     * @param $file
     * @return array
     */
    public function getLocalCopies(File $file)
    {
        $result = $this->get($this->getHashedKey($file));
        return is_array($result) ? $result : [];
    }


    /**
     * @param File $file
     * @return string
     */
    private function getHashedKey(File $file)
    {
        return md5($file->getPrefix());
    }

    /**
     * @param File $file
     */
    public function removeFiles(File $file)
    {
        $this->remove($this->getHashedKey($file));
    }
}