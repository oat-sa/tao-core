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
        
            $file = dirname(__FILE__).DIRECTORY_SEPARATOR.'indexation_2_7_1.rdf';
        
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
        
        if ($currentVersion == '2.7.3') {
        
            $file = dirname(__FILE__).DIRECTORY_SEPARATOR.'indexation_2_7_4.rdf';
        
            $adapter = new tao_helpers_data_GenerisAdapterRdf();
            if ($adapter->import($file)) {
                $currentVersion = '2.7.4';
            } else{
                common_Logger::w('Import failed for '.$file);
            }
        }
        
        if ($currentVersion == '2.7.4') {
            $file = dirname(__FILE__).DIRECTORY_SEPARATOR.'model_2_7_5.rdf';
            
            $adapter = new tao_helpers_data_GenerisAdapterRdf();
            if ($adapter->import($file)) {
                $currentVersion = '2.7.5';
            } else{
                common_Logger::w('Import failed for '.$file);
            }
        }
        
        if ($currentVersion == '2.7.5') {
            $file = dirname(__FILE__).DIRECTORY_SEPARATOR.'index_type_2_7_6.rdf';
        
            $adapter = new tao_helpers_data_GenerisAdapterRdf();
            if ($adapter->import($file)) {
                $currentVersion = '2.7.6';
            } else{
                common_Logger::w('Import failed for '.$file);
            }
        }
        
        if ($currentVersion == '2.7.6') {
            
            $query = "DELETE FROM statements "
                ."WHERE modelId = 1 "
                ."AND NOT subject LIKE '".LOCAL_NAMESPACE."%' "
                ."AND predicate IN ('".RDFS_LABEL."','".RDFS_COMMENT."') "
                ."AND NOT l_language = ''";
            $success = \common_persistence_SqlPersistence::getPersistence('default')->exec($query);

            // move translations to correct modelid
            $langService = \tao_models_classes_LanguageService::singleton();
            $dataUsage = new \core_kernel_classes_Resource(INSTANCE_LANGUAGE_USAGE_DATA);
            foreach ($langService->getAvailableLanguagesByUsage($dataUsage) as $lang) {
                $langService->addTranslationsForLanguage($lang);
            }
            $currentVersion = '2.7.7';
        }
        
        return $currentVersion;
    }
}
