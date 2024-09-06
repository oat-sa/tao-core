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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\Translation\Factory;

use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\tao\model\Translation\Entity\ResourceTranslation;

class ResourceTranslationFactory
{
    private Ontology $ontology;

    public function __construct(Ontology $ontology)
    {
        $this->ontology = $ontology;
    }

    public function create(
        core_kernel_classes_Resource $originResource,
        core_kernel_classes_Resource $translationResource,
    ): ResourceTranslation {
        $valueProperty = $this->ontology->getProperty('http://www.w3.org/1999/02/22-rdf-syntax-ns#value');
        $progressProperty = $this->ontology->getProperty(ResourceTranslation::PROPERTY_TRANSLATION_PROGRESS);
        $languageProperty = $this->ontology->getProperty(ResourceTranslation::PROPERTY_LANGUAGE);

        /** @var core_kernel_classes_Resource $language */
        $language = $translationResource->getUniquePropertyValue($languageProperty);
        $languageCode = $language->getUniquePropertyValue($valueProperty);

        /** @var core_kernel_classes_Resource $progress */
        $progress = $translationResource->getUniquePropertyValue($progressProperty);

        return new ResourceTranslation(
            $originResource->getUri(),
            $translationResource->getUri(),
            $translationResource->getLabel(),
            ResourceTranslation::PROGRESS_MAPPING[$progress->getUri()],
            $progress->getUri(),
            (string)$languageCode,
            $language->getUri()
        );
    }
}
