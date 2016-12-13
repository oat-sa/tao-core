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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */
namespace oat\tao\scripts\install;

use oat\tao\model\websource\TokenWebSource;
use oat\oatbox\filesystem\FileSystemService;
use oat\tao\model\websource\TokenWebSourceService;
use tao_models_classes_service_FileStorage;
use League\Flysystem\Adapter\Local;
use oat\tao\model\websource\FlyTokenWebSource;

/**
 * This post-installation script creates a new local file source for services
 */
class SetServiceFileStorage extends \common_ext_action_InstallAction
{
    public function __invoke($params)
    {
        $publicDataPath = FILES_PATH.'tao'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR;
        $privateDataPath = FILES_PATH.'tao'.DIRECTORY_SEPARATOR.'private'.DIRECTORY_SEPARATOR;
        
        if (file_exists($publicDataPath)) {
            \helpers_File::emptyDirectory($publicDataPath);
        }
        if (file_exists($privateDataPath)) {
            \helpers_File::emptyDirectory($privateDataPath);
        }

        $fsService = $this->getServiceLocator()->get(FileSystemService::SERVICE_ID);

        $options = $fsService->getOptions();
        $adapters = $options[FileSystemService::OPTION_ADAPTERS];
        $toRegistered = false;
        if (! array_key_exists('public', $adapters)) {
            $fsService->createFileSystem('public', 'tao/public');
            $toRegistered = true;
        }
        if (! array_key_exists('private', $adapters)) {
            $fsService->createFileSystem('private', 'tao/private');
            $toRegistered = true;
        }
        if ($toRegistered) {
            $this->registerService(FileSystemService::SERVICE_ID, $fsService);
        }

        $websource = TokenWebSourceService::spawnTokenWebsource('public');

        $service = new tao_models_classes_service_FileStorage(array(
            tao_models_classes_service_FileStorage::OPTION_PUBLIC_FS => 'public',
            tao_models_classes_service_FileStorage::OPTION_PRIVATE_FS => 'private',
            tao_models_classes_service_FileStorage::OPTION_ACCESS_PROVIDER => $websource->getId()
        ));
        $this->registerService(tao_models_classes_service_FileStorage::SERVICE_ID, $service, false);
    }
}
