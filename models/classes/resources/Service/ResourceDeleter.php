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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\resources\Service;

use Throwable;
use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use oat\generis\model\OntologyRdf;
use oat\tao\model\resources\Contract\ResourceDeleterInterface;
use oat\tao\model\resources\Context\ResourceRepositoryContext;
use oat\tao\model\resources\Exception\ResourceDeletionException;
use oat\tao\model\resources\Contract\ResourceRepositoryInterface;

class ResourceDeleter implements ResourceDeleterInterface
{
    /** @var ResourceRepositoryInterface */
    private $resourceRepository;

    public function __construct(ResourceRepositoryInterface $resourceRepository)
    {
        $this->resourceRepository = $resourceRepository;
    }

    public function delete(core_kernel_classes_Resource $resource): void
    {
        try {
            $this->resourceRepository->delete(
                new ResourceRepositoryContext(
                    [
                        ResourceRepositoryContext::PARAM_RESOURCE => $resource,
                        ResourceRepositoryContext::PARAM_PARENT_CLASS => $this->getParentClass($resource),
                    ]
                )
            );
        } catch (Throwable $exception) {
            throw new ResourceDeletionException(
                sprintf(
                    'Unable to delete resource "%s::%s" (%s).',
                    $resource->getLabel(),
                    $resource->getUri(),
                    $exception->getMessage()
                ),
                __('Unable to delete the selected resource')
            );
        }

        if ($resource->exists()) {
            throw new ResourceDeletionException(
                'Unable to delete the selected resource.',
                __('Unable to delete the selected resource.')
            );
        }
    }

    private function getParentClass(core_kernel_classes_Resource $resource): core_kernel_classes_Class
    {
        $parentClassUri = $resource->getOnePropertyValue($resource->getProperty(OntologyRdf::RDF_TYPE));

        return $resource->getClass($parentClassUri);
    }
}
