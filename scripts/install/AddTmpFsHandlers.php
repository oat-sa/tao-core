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
     */
    public function __invoke($params)
    {
        $this->registerService(UploadService::SERVICE_ID, new UploadService([]));

        /** @var FileSystemService $fsm */
        $fsm = $this->getServiceLocator()->get(FileSystemService::SERVICE_ID);
        if (!array_key_exists(UploadService::$tmpFilesystemId, $fsm->getOption(FileSystemService::OPTION_ADAPTERS))) {
            $fsm->createFileSystem(UploadService::$tmpFilesystemId, 'tmp');
            $this->registerService(FileSystemService::SERVICE_ID, $fsm);
        }
    }
}
