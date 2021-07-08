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

declare(strict_types = 1);

namespace oat\tao\model\extension;

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\i18n\LanguageService;
use common_ext_Extension;
use oat\tao\helpers\translation\rdf\RdfPack;

class ExtensionModel extends \common_ext_ExtensionModel
{
    use OntologyAwareTrait;

    /**
     * @throws \core_kernel_classes_EmptyProperty
     * @throws \common_Exception
     */
    public function __construct(common_ext_Extension $extension)
    {
        parent::__construct($extension);

        $this->addLanguages($extension);
    }

    /**
     * @throws \core_kernel_classes_EmptyProperty
     * @throws \common_Exception
     * @throws \Exception
     */
    protected function addLanguages($extension): void
    {
        /** @var LanguageService $languageService */
        $languageService = ServiceManager::getServiceManager()->get(LanguageService::SERVICE_ID);

        $usage = $this->getResource(LanguageService::INSTANCE_LANGUAGE_USAGE_DATA);

        foreach ($languageService->getAvailableLanguagesByUsage($usage) as $language) {
            $code = $languageService->getCodeByLanguage($language);
            $this->append((new RdfPack($code, $extension))->getIterator());
        }
    }
}
