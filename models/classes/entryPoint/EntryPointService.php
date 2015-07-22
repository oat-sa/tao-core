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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */
namespace oat\tao\model\entryPoint;

use oat\oatbox\AbstractRegistry;
use \common_ext_ExtensionsManager;
use \common_Logger;
use oat\tao\helpers\Template;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\menu\MenuService;
use oat\tao\model\entryPoint\BackOfficeEntrypoint;
use oat\tao\model\entryPoint\Entrypoint;

/**
 * 
 * Registry to store client library paths that will be provide to requireJs
 *
 * @author Lionel Lecaque, lionel@taotesting.com
 */
class EntryPointService extends AbstractRegistry
{
    public function registerEntryPoint(Entrypoint $e)
    {
        $this->set($e->getId(), $e);
    }
    
    /**
     * Specify in which extensions the config will be stored
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     * @return common_ext_Extension
     */
    protected function getExtension() {
        return \common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
    }
    
    /**
     *
     * config file in which the data will be stored
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     * @return string
    */
    protected function getConfigId() {
        return 'entrypoint';
    }
    
    public function getEntryPoints()
    {
        $entryPoints = array();
        foreach (MenuService::getEntryPoints() as $entry) {
            $entryPoints[$entry->getId()] = $entry;
        }
        foreach ($this->getMap() as $entry) {
            $entryPoints[$entry->getId()] = $entry;
        }
        return $entryPoints;
    }
}
