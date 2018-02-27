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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\extension;

use common_ext_Extension;
use core_kernel_classes_Resource;
use oat\tao\helpers\translation\rdf\RdfPack;
use tao_models_classes_LanguageService;

class ExtensionModel extends \common_ext_ExtensionModel
{
    /**
     * @var null
     */
    private $modelId;

    /**
     * @param common_ext_Extension $extension
     * @param null $modelId
     *
     * @throws \common_exception_Error
     * @throws \common_exception_InvalidArgumentType
     * @throws \common_exception_MissingParameter
     * @throws \common_ext_InstallationException
     * @throws \common_ext_ManifestNotFoundException
     */
    public function __construct(common_ext_Extension $extension, $modelId) {
        $this->modelId = $modelId;

        parent::__construct($extension, $modelId);
        $this->addLanguages($extension);
    }

    /**
     * @param $extension
     *
     * @throws \common_exception_Error
     * @throws \common_exception_InvalidArgumentType
     */
    protected function addLanguages($extension) {
        $langService = tao_models_classes_LanguageService::singleton();
        $dataUsage = new core_kernel_classes_Resource(tao_models_classes_LanguageService::INSTANCE_LANGUAGE_USAGE_DATA);

        foreach ($langService->getAvailableLanguagesByUsage($dataUsage) as $lang) {
            $langCode = $langService->getCode($lang);
            $pack = new RdfPack($langCode, $extension, $this->modelId);
            $this->append($pack->getIterator());
        }
    }
}
