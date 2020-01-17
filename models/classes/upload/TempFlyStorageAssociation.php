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

use oat\oatbox\AbstractRegistry;
use oat\oatbox\filesystem\File;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;

class TempFlyStorageAssociation extends AbstractRegistry implements TmpLocalAwareStorageInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    protected function getExtension()
    {
        return $this->getServiceLocator()->get(\common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('tao');
    }

    protected function getConfigId()
    {
        return 'tmp_fly_files_registry';
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return AbstractRegistry|TmpLocalAwareStorageInterface
     */
    public static function getStorage(ServiceLocatorInterface $serviceLocator)
    {
        /** @var TempFlyStorageAssociation $registry */
        $registry = self::getRegistry();
        $registry->setServiceLocator($serviceLocator);
        return $registry;
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