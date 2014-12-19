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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\tao\scripts\update;

use common_ext_ExtensionsManager;
use tao_helpers_data_GenerisAdapterRdf;
use common_Logger;
use oat\tao\model\ClientLibRegistry;

/**
 * 
 * @author Joel Bout <joel@taotesting.com>
 */
class Updater extends \common_ext_ExtensionUpdater {
    
    /**
     * 
     * @param string $currentVersion
     * @return string $versionUpdatedTo
     */
    public function update($initialVersion) {
        
        $currentVersion = $initialVersion;
        $extensionManager = common_ext_ExtensionsManager::singleton();
        //migrate from 2.6 to 2.7.0
        if ($currentVersion == '2.6') {

            //create Js config  
            $ext = $extensionManager->getExtensionById('tao');
            $config = array(
                'timeout' => 30
            );
            $ext->setConfig('js', $config);

            $currentVersion = '2.7.0';
        }
        
        //migrate from 2.7.0 to 2.7.1
        if ($currentVersion == '2.7.0') {
        
            $ext = $extensionManager->getExtensionById('tao');
            $file = $ext->getDir().'models'.DIRECTORY_SEPARATOR.'ontology'.DIRECTORY_SEPARATOR.'indexation.rdf';
        
            $adapter = new tao_helpers_data_GenerisAdapterRdf();
            if ($adapter->import($file)) {
                $currentVersion = '2.7.1';
            } else{
                common_Logger::w('Import failed for '.$file);
            }
        }
        
        if ($currentVersion == '2.7.1') {
            foreach ($extensionManager->getInstalledExtensions() as $extension) {
                $extManifestConsts = $extension->getConstants();
                if (isset($extManifestConsts['BASE_WWW'])) {
                    
                    ClientLibRegistry::getRegistry()->register($extension->getId(), $extManifestConsts['BASE_WWW'] . 'js');
                    ClientLibRegistry::getRegistry()->register($extension->getId() . 'Css', $extManifestConsts['BASE_WWW'] . 'css');
                    
                }
            }
            
            $currentVersion = '2.7.2';
            
        }

        if ($currentVersion == '2.7.2') {
            // zendSearch Update only
            $currentVersion = '2.7.3';
        }
        
        return $currentVersion;
    }
}
