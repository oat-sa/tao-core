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
use common_ext_ExtensionsManager;
use common_persistence_SqlPersistence;
use core_kernel_persistence_smoothsql_SmoothIterator;
use helpers_RdfDiff;
use oat\generis\model\data\ModelManager;
use oat\generis\model\kernel\persistence\file\FileModel;
use oat\tao\model\extension\ExtensionModel;

class OntologyUpdater
{
    /**
     * @throws \common_exception_Error
     * @throws \common_exception_InconsistentData
     * @throws \common_exception_InvalidArgumentType
     * @throws \common_exception_MissingParameter
     * @throws \common_ext_ExtensionException
     * @throws \common_ext_InstallationException
     * @throws \common_ext_ManifestNotFoundException
     */
    public static function syncModels()
    {
        $currentModel = ModelManager::getModel();

        //exclude generis modelId

        $modelId = common_ext_ExtensionsManager::singleton()->getModelIdByExtensionId('generis');

        $readableModelIds = array_diff($currentModel->getReadableModels(), array($modelId));
        
        $persistence = common_persistence_SqlPersistence::getPersistence('default');
        
        $smoothIterator = new core_kernel_persistence_smoothsql_SmoothIterator($persistence, $readableModelIds);
        
        $nominalModel = new AppendIterator();
        foreach (common_ext_ExtensionsManager::singleton()->getInstalledExtensions() as $ext) {
            $nominalModel->append(new ExtensionModel($ext, $modelId));
        }
        
        $diff = helpers_RdfDiff::create($smoothIterator, $nominalModel);
        self::logDiff($diff);
        
        $diff->applyTo($currentModel);
    }

    /**
     * @param string $extensionId
     *
     * @throws \common_exception_Error
     * @throws \common_exception_InconsistentData
     * @throws \common_exception_InvalidArgumentType
     * @throws \common_exception_MissingParameter
     * @throws \common_ext_ExtensionException
     * @throws \common_ext_InstallationException
     * @throws \common_ext_ManifestNotFoundException
     */
    public function syncModel($extensionId)
    {
        $currentModel = ModelManager::getModel();

        $extensionManager = common_ext_ExtensionsManager::singleton();

        $modelId = $extensionManager->getModelIdByExtensionId($extensionId);

        $persistence = common_persistence_SqlPersistence::getPersistence('default');

        $smoothIterator = new core_kernel_persistence_smoothsql_SmoothIterator($persistence, [$modelId]);

        $nominalModel = new AppendIterator();

        $nominalModel->append(
            new ExtensionModel($extensionManager->getExtensionById($extensionId), $modelId)
        );

        $diff = helpers_RdfDiff::create($smoothIterator, $nominalModel);

        self::logDiff($diff);
        $diff->applyTo($currentModel);
    }

    /**
     * @param $rdfFile
     * @throws \common_exception_InconsistentData
     * @throws \common_exception_MissingParameter
     */
    public static function correctModelId($rdfFile)
    {
        $modelFile = new FileModel(array('file' => $rdfFile));
        $modelRdf = ModelManager::getModel()->getRdfInterface();
        foreach ($modelFile->getRdfInterface() as $triple) {
            $modelRdf->remove($triple);
            $modelRdf->add($triple);
        }
    }

    /**
     * @param helpers_RdfDiff $diff
     *
     * @throws \common_exception_Error
     */
    protected static function logDiff(\helpers_RdfDiff $diff)
    {
        $folder = FILES_PATH.'updates'.DIRECTORY_SEPARATOR;
        $updateId = time();
        while (file_exists($folder.$updateId)) {
            $count = isset($count) ? $count + 1 : 0;
            $updateId = time().'_'.$count;
        }
        $path = $folder.$updateId;
        if (!mkdir($path, 0700, true)) {
            throw new \common_exception_Error('Unable to log update to '.$path);
        }
        
        FileModel::toFile($path.DIRECTORY_SEPARATOR.'add.rdf', $diff->getTriplesToAdd());
        FileModel::toFile($path.DIRECTORY_SEPARATOR.'remove.rdf', $diff->getTriplesToRemove());
    }
}
