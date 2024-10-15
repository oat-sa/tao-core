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
use oat\tao\model\TaoOntology;
use oat\tao\model\Translation\Entity\ResourceTranslation;
use oat\tao\model\Translation\Service\ResourceMetadataPopulateService;

class ResourceTranslationFactory
{
    private ResourceMetadataPopulateService $metadataPopulateService;

    public function __construct(ResourceMetadataPopulateService $metadataPopulateService)
    {
        $this->metadataPopulateService = $metadataPopulateService;
    }

    public function create(
        core_kernel_classes_Resource $originResource,
        core_kernel_classes_Resource $translationResource
    ): ResourceTranslation {
        $resource = new ResourceTranslation($translationResource->getUri(), $translationResource->getLabel());
        $resource->setOriginResourceUri($originResource->getUri());
        $resource->addMetadataUri(TaoOntology::PROPERTY_TRANSLATION_PROGRESS);

        $this->metadataPopulateService->populate($resource, $translationResource);

        return $resource;
    }
}
