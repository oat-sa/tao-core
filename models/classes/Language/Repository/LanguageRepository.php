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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 *
 * @author Gabriel Felipe Soares <gabriel.felipe.soares@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\model\Language\Repository;

use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\generis\model\OntologyRdf;
use oat\tao\model\Language\Business\Contract\LanguageRepositoryInterface;
use oat\tao\model\Language\Language;
use oat\tao\model\Language\LanguageCollection;
use tao_models_classes_LanguageService;

class LanguageRepository implements LanguageRepositoryInterface
{
    /** @var Ontology */
    private $ontology;

    /** @var tao_models_classes_LanguageService */
    private $languageService;

    public function __construct(Ontology $ontology, tao_models_classes_LanguageService $languageService)
    {
        $this->ontology = $ontology;
        $this->languageService = $languageService;
    }

    public function findAvailableLanguagesByUsage(): LanguageCollection
    {
        $languages = $this->languageService->getAvailableLanguagesByUsage(
            $this->ontology->getResource(tao_models_classes_LanguageService::INSTANCE_LANGUAGE_USAGE_DATA)
        );

        $output = new LanguageCollection();

        /** @var core_kernel_classes_Resource[] $languages */
        foreach ($languages as $language) {
            $values = $language->getPropertiesValues(
                [
                    OntologyRdf::RDF_VALUE,
                    tao_models_classes_LanguageService::PROPERTY_LANGUAGE_ORIENTATION
                ]
            );

            $orientationUri = $values[tao_models_classes_LanguageService::PROPERTY_LANGUAGE_ORIENTATION][0]->getUri();

            $output->add(
                new Language(
                    $language->getUri(),
                    $values[OntologyRdf::RDF_VALUE][0]->__toString(),
                    $language->getLabel(),
                    $orientationUri === tao_models_classes_LanguageService::INSTANCE_ORIENTATION_RTL ? 'rtl' : 'ltr'
                )
            );
        }

        return $output;
    }
}
