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

namespace oat\tao\model\resources\Repository;

use oat\tao\model\Context\ContextInterface;
use oat\tao\model\resources\Context\ResourceRepositoryContext;
use oat\tao\model\resources\Contract\ResourceRepositoryInterface;

class ResourceRepositoryProxy implements ResourceRepositoryInterface
{
    /** @var ResourceRepositoryInterface */
    private $resourceRepository;

    /** @var ResourceRepositoryInterface */
    private $classRepository;

    public function __construct(
        ResourceRepositoryInterface $resourceRepository,
        ResourceRepositoryInterface $classRepository
    ) {
        $this->resourceRepository = $resourceRepository;
        $this->classRepository = $classRepository;
    }

    public function delete(ContextInterface $context): void
    {
        $this->getRepositoryByContext($context)->delete($context);
    }

    private function getRepositoryByContext(ContextInterface $context): ResourceRepositoryInterface
    {
        $repository = $context->getParameter(ResourceRepositoryContext::PARAM_REPOSITORY, 'resource');

        return $repository === ResourceRepositoryContext::REPO_CLASS
            ? $this->classRepository
            : $this->resourceRepository;
    }
}
