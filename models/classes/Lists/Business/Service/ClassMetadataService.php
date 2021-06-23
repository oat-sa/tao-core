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
 * Copyright (c) 2020-2021 (original work) Open Assessment Technologies SA;
 *
 */

declare(strict_types=1);

namespace oat\tao\model\Lists\Business\Service;

use core_kernel_classes_Class;
use oat\generis\model\OntologyAwareTrait;
use oat\tao\model\Lists\Business\Contract\ClassMetadataSearcherInterface;
use oat\tao\model\Lists\Business\Domain\ClassCollection;
use oat\tao\model\Lists\Business\Domain\ClassMetadata;
use oat\tao\model\Lists\Business\Domain\MetadataCollection;
use oat\tao\model\Lists\Business\Input\ClassMetadataSearchInput;
use oat\tao\model\service\InjectionAwareService;

class ClassMetadataService extends InjectionAwareService implements ClassMetadataSearcherInterface
{
    use OntologyAwareTrait;

    /** @var int */
    private $maxListSize;

    public const SERVICE_ID = 'tao/ClassMetadataService';

    public function findAll(ClassMetadataSearchInput $input): ClassCollection
    {
        $searchRequest = $input->getSearchRequest();

        $this->maxListSize = $searchRequest->getMaxListSize();

        $class = $this->getClass($searchRequest->getClassUri());
        $collection = new ClassCollection();

        if (!$class->isClass()) {
            return $collection;
        }

        return $this->fillData($collection, $class);
    }

    private function fillData(
        ClassCollection $collection,
        core_kernel_classes_Class $currentClass,
        core_kernel_classes_Class $parentClass = null
    ): ClassCollection
    {
        $subClasses = $currentClass->getSubClasses();

        if (count($subClasses)) {
            $classMetadata = (new ClassMetadata())
                ->setClass($currentClass->getUri())
                ->setLabel($currentClass->getLabel())
                ->setParentClass($parentClass !== null ? $parentClass->getUri() : null)
                ->setMetaData($this->getClassMetadata($currentClass));

            $collection->addClassMetadata($classMetadata);

            foreach ($subClasses as $subClass) {
                $collection = $this->fillData($collection, $subClass, $currentClass);
            }

            return $collection;
        }

        $classMetadata = (new ClassMetadata())
            ->setClass($currentClass->getUri())
            ->setLabel($currentClass->getLabel())
            ->setParentClass($parentClass !== null ? $parentClass->getUri() : null)
            ->setMetaData($this->getClassMetadata($currentClass));

        $collection->addClassMetadata($classMetadata);

        return $collection;
    }

    private function getClassMetadata(core_kernel_classes_Class $class): MetadataCollection
    {
        return $this->getClassMetadataValuesService()->getByClassRecursive($class, $this->maxListSize);
    }

    private function getClassMetadataValuesService(): GetClassMetadataValuesService
    {
        return $this->getServiceLocator()->get(GetClassMetadataValuesService::class);
    }
}
