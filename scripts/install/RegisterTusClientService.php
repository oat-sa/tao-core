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
 * Copyright (c) 2019  (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\scripts\install;

use common_report_Report;
use oat\oatbox\extension\InstallAction;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\ServiceNotFoundException;
use oat\tao\model\tusUpload\clients\GuzzleClientAdapter;
use oat\tao\model\tusUpload\TusFileStorageService;
use oat\tao\model\tusUpload\TusUploadClientService;
use oat\tao\model\tusUpload\TusUploadClientServiceInterface;


/**
 * Registering TusUpload storage
 * php index.php 'oat\taoSyncClient\scripts\install\RegisterTusClientService'
 *
 * Class RegisterTusClientService
 */
class RegisterTusClientService extends InstallAction
{
    /**
     * @param $params
     * @return common_report_Report
     * @throws \common_Exception
     */
    public function __invoke($params)
    {
        try {
            $tusUploadClientService = $this->getServiceManager()->get(TusUploadClientServiceInterface::SERVICE_ID);
        } catch (ServiceNotFoundException $e) {
            $tusUploadClientService = new TusUploadClientService([
                TusUploadClientService::OPTION_FILE_STORAGE        => new TusFileStorageService(),
                TusUploadClientService::OPTION_CLIENT              => new GuzzleClientAdapter(),
                TusUploadClientService::OPTION_CHUNK_SIZE          => 2000,
                TusUploadClientService::OPTION_SERVER_DOMAIN       => '',
                TusUploadClientService::OPTION_SERVER_RELATIVE_URL => ''
            ]);
            $tusUploadClientService->setServiceLocator($this->getServiceLocator());
        }
        $tusFileStorageService = $tusUploadClientService->getOption(TusUploadClientService::OPTION_FILE_STORAGE);

        if ($tusFileStorageService->getStorageName()) {
            $serviceManager = $this->getServiceManager();
            $service = $serviceManager->get(FileSystemService::SERVICE_ID);
            $service->createFileSystem($tusFileStorageService->getStorageName());
            $serviceManager->register(FileSystemService::SERVICE_ID, $service);
        }
        $tusFileStorageService->setServiceLocator($this->getServiceLocator());
        $tusFileStorageService->createStorage();

        $this->getServiceManager()->register(TusUploadClientServiceInterface::SERVICE_ID, $tusUploadClientService);
        return new common_report_Report(common_report_Report::TYPE_SUCCESS, __('Tus upload client storage successfully created'));
    }
}
