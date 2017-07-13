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
 * 
 */
namespace oat\tao\scripts\install;

use oat\oatbox\extension\InstallAction;
use oat\oatbox\filesystem\FileSystemService;
use oat\tao\model\state\StateMigration;

/**
 * This post-installation script creates a new local file source for logs
 */
class AddArchiveService extends InstallAction
{
    public function __invoke($params)
    {
        /** @var FileSystemService $fsm */
        $fsm = $this->getServiceManager()->get(FileSystemService::SERVICE_ID);
        $fsm->createFileSystem('stateBackup');
        $this->registerService(FileSystemService::SERVICE_ID, $fsm);
        
        $this->registerService(StateMigration::SERVICE_ID, new StateMigration(array(StateMigration::OPTION_FILESYSTEM => 'stateBackup')));
    }
}