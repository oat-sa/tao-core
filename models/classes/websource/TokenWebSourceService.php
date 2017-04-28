<?php
/**
 * 
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
namespace oat\tao\model\websource;

use League\Flysystem\Adapter\Local;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\ServiceManager;

/**
 * Grants Access to compiled data via the MVC
 *
 * @access public
 * @author Antoine Robin, <antoine@taotesting.com>
 * @package tao
 
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */
class TokenWebSourceService
{

    /**
     * @param $fileSystemId
     * @return \tao_models_classes_fsAccess_AccessProvider
     */
    public static function spawnTokenWebsource($fileSystemId){

        /** @var FileSystemService $fsService */
        $fsService = self::getServiceManager()->get(FileSystemService::SERVICE_ID);

        $fs = $fsService->getFileSystem($fileSystemId);

        if ($fs->getAdapter() instanceof Local) {
            $websource = TokenWebSource::spawnWebsource($fileSystemId, $fs->getAdapter()->getPathPrefix());
        } else {
            $websource = FlyTokenWebSource::spawnWebsource($fileSystemId,'');
        }

        return $websource;
    }

    public static function getServiceManager(){
        return ServiceManager::getServiceManager();
    }
}