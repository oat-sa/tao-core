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

use AppendIterator;
use oat\generis\model\kernel\persistence\file\FileModel;
use oat\generis\model\data\ModelManager;
use helpers_RdfDiff;
use core_kernel_persistence_smoothsql_SmoothModel;
use common_persistence_SqlPersistence;
use common_ext_ExtensionsManager;
use core_kernel_persistence_smoothsql_SmoothIterator;
use oat\tao\model\extension\ExtensionModel;

class OntologyUpdater {
    
    static public function syncModels() {
        $modelIds = array_diff(
            core_kernel_persistence_smoothsql_SmoothModel::getReadableModelIds(),
            core_kernel_persistence_smoothsql_SmoothModel::getUpdatableModelIds()
        );
        
        $persistence = common_persistence_SqlPersistence::getPersistence('default');
        
        $smoothIterator = new core_kernel_persistence_smoothsql_SmoothIterator($persistence, $modelIds);
        
        $nominalModel = new AppendIterator();
        foreach (common_ext_ExtensionsManager::singleton()->getInstalledExtensions() as $ext) {
            $nominalModel->append(new ExtensionModel($ext));
        }
        
        $diff = helpers_RdfDiff::create($smoothIterator, $nominalModel);
        
        $smooth  = ModelManager::getModel();
        $diff->applyTo($smooth);
    }
    
    static public function correctModelId($rdfFile) {
        $modelFile = new FileModel(array('file' => $rdfFile));
        $modelRdf = ModelManager::getModel()->getRdfInterface();
        foreach ($modelFile->getRdfInterface() as $triple) {
            $modelRdf->remove($triple);
            $modelRdf->add($triple);
        }
    }
    
}
