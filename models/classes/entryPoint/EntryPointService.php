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
use oat\oatbox\service\ServiceManager;

/**
 * 
 * Registry to store client library paths that will be provide to requireJs
 *
 * @author Lionel Lecaque, lionel@taotesting.com
 */
class EntryPointService extends ConfigurableService
{
    const OPTION_ENTRYPOINTS = 'existing';
    
    const OPTION_PRELOGIN = 'prelogin';
    
    const OPTION_POSTLOGIN = 'postlogin';
    
    public function addEntryPoint(Entrypoint $e, $target = self::OPTION_POSTLOGIN)
    {
        $entryPoints = $this->getOption(self::OPTION_ENTRYPOINTS);
        $entryPoints[$e->getId()] = $e;
        $this->setOption(self::OPTION_ENTRYPOINTS, $entryPoints);
        
        $available = $this->hasOption($target) ? $this->getOption($target) : array();
        if (!in_array($e->getId(), $available)) {
            $available[] = $e->getId();
            $this->setOption($target, $available);
        } 
    }
    
    
    public function getEntryPoints($target = self::OPTION_POSTLOGIN)
    {
        $entryPoints = array();
        if ($target == self::OPTION_POSTLOGIN) {
            foreach (MenuService::getEntryPoints() as $entry) {
                $entryPoints[$entry->getId()] = $entry;
            }
        }
        
        $ids = $this->hasOption($target) ? $this->getOption($target) : array();
        $existing = $this->getOption(self::OPTION_ENTRYPOINTS);
        foreach ($ids as $id) {
            $entryPoints[$id] = $existing[$id];
        }
        return $entryPoints;
    }

    /**
     * @return EntryPointService
     * @deprecated
     */
    public static function getRegistry()
    {
        return ServiceManager::getServiceManager()->get('tao/entrypoint');
    }
    
    /**
     *
     * @param Entrypoint $e
     * @param string $target
     * @deprecated
     */
    public function registerEntryPoint(Entrypoint $e, $target = self::OPTION_POSTLOGIN)
    {
        $this->addEntryPoint($e, $target);
        $this->getServiceManager()->register('tao/entrypoint', $this);
    }
}
