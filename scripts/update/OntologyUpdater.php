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
use helpers_RdfDiff;
use oat\generis\Helper\PropertyCache;
use oat\generis\model\data\Model;
use oat\generis\model\data\ModelManager;
use oat\generis\model\GenerisRdf;
use oat\generis\model\kernel\persistence\file\FileModel;
use oat\tao\model\extension\ExtensionModel;
use oat\tao\model\user\TaoRoles;

class OntologyUpdater
{
    public static function syncModels()
    {
        $currentModel = ModelManager::getModel();

        $existingTriples = self::getCurrentTriples($currentModel);
        $nominalTriples = self::getNominalTriples();

        $diff = helpers_RdfDiff::create($existingTriples, $nominalTriples);
        self::logDiff($diff);

        $diff->applyTo($currentModel);
        PropertyCache::clearCachedValuesByTriples($diff->getTriplesToRemove());
    }

    public static function correctModelId($rdfFile)
    {
        $modelFile = new FileModel(['file' => $rdfFile]);
        $modelRdf = ModelManager::getModel()->getRdfInterface();
        foreach ($modelFile->getRdfInterface() as $triple) {
            $modelRdf->remove($triple);
            $modelRdf->add($triple);
        }
    }

    protected static function logDiff(\helpers_RdfDiff $diff)
    {
        $folder = FILES_PATH . 'updates' . DIRECTORY_SEPARATOR;
        $updateId = time();
        while (file_exists($folder . $updateId)) {
            $count = isset($count) ? $count + 1 : 0;
            $updateId = time() . '_' . $count;
        }
        $path = $folder . $updateId;
        if (!mkdir($path, 0700, true)) {
            throw new \common_exception_Error('Unable to log update to ' . $path);
        }

        FileModel::toFile($path . DIRECTORY_SEPARATOR . 'add.rdf', $diff->getTriplesToAdd());
        FileModel::toFile($path . DIRECTORY_SEPARATOR . 'remove.rdf', $diff->getTriplesToRemove());
    }

    public static function getNominalTriples(): \Traversable
    {
        $nominalModel = new AppendIterator();
        foreach (common_ext_ExtensionsManager::singleton()->getInstalledExtensions() as $ext) {
            $nominalModel->append(new ExtensionModel($ext));
        }
        $langModel = \tao_models_classes_LanguageService::singleton()->getLanguageDefinition();
        $nominalModel->append($langModel);
        return $nominalModel;
    }

    public static function getCurrentTriples(Model $currentModel): \Traversable
    {
        return new \CallbackFilterIterator(
            $currentModel->getRdfInterface()->getIterator(),
            function (\core_kernel_classes_Triple $item) {
                /**
                 * Those includes generated with a script and created in non-system space, so we ignore them.
                 * @see \tao_install_ExtensionInstaller::installManagementRole
                 */
                $isAutomaticIncludeRole = $item->subject === TaoRoles::GLOBAL_MANAGER
                    && $item->predicate === GenerisRdf::PROPERTY_ROLE_INCLUDESROLE;

                // GrantAccess field added to entities in non-system space and also should be ignored for now.
                $isGrantAccess = $item->predicate === 'http://www.tao.lu/Ontologies/taoFuncACL.rdf#GrantAccess';

                return !$isGrantAccess && !$isAutomaticIncludeRole;
            }
        );
    }
}
