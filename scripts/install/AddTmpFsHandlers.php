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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA
 *
 */
namespace oat\tao\scripts\install;

use oat\oatbox\event\EventManager;
use oat\oatbox\extension\InstallAction;
use oat\oatbox\filesystem\FileSystemService;
use oat\tao\model\event\FileUploadedEvent;
use oat\tao\model\event\UploadLocalCopyCreatedEvent;
use oat\tao\model\upload\UploadService;

/**
 * This  script creates a new local file source for upload storage
 */
class AddTmpFsHandlers extends InstallAction
{

    /**
     * @param $params
     * @throws \common_Exception
     * @throws \oat\oatbox\service\ServiceNotFoundException
     * @throws \common_exception_Error
     */
    public function __invoke($params)
    {
        /** @var FileSystemService $fsm */
        $fsm = $this->getServiceManager()->get(FileSystemService::SERVICE_ID);

        $this->getServiceManager()->register(UploadService::SERVICE_ID, new UploadService([]));

        $uploadFSId = UploadService::$tmpFilesystemId;

        if (!array_key_exists($uploadFSId, $fsm->getOption(FileSystemService::OPTION_ADAPTERS))
        ) {
            $fsm->createFileSystem($uploadFSId, 'tmp');
            $this->getServiceManager()->register(FileSystemService::SERVICE_ID, $fsm);
        }

        /** @var EventManager $eventManager */
        $eventManager = $this->getServiceManager()->get(EventManager::CONFIG_ID);
        $eventManager->attach(FileUploadedEvent::class, [UploadService::class, 'listenUploadEvent']);
        $eventManager->attach(UploadLocalCopyCreatedEvent::class, [UploadService::class, 'listenLocalCopyEvent']);
        $this->getServiceManager()->register(EventManager::CONFIG_ID, $eventManager);
    }

}
