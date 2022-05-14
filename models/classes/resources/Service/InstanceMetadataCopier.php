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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 *
 * @author Andrei Shapiro <andrei.shapiro@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\model\resources\Service;

use helpers_File;
use core_kernel_classes_Literal;
use core_kernel_classes_Resource;
use oat\generis\model\GenerisRdf;
use core_kernel_classes_Property;
use oat\generis\model\OntologyRdf;
use oat\oatbox\filesystem\FileSystemService;
use oat\generis\model\fileReference\FileReferenceSerializer;
use oat\tao\model\resources\Contract\ClassMetadataMapperInterface;
use oat\tao\model\resources\Contract\InstanceMetadataCopierInterface;

class InstanceMetadataCopier implements InstanceMetadataCopierInterface
{
    /** @var ClassMetadataMapperInterface */
    private $classMetadataMapper;

    /** @var FileReferenceSerializer */
    private $fileReferenceSerializer;

    /** @var FileSystemService */
    private $fileSystemService;

    /** @var string[] */
    private $blacklistedProperties = [
        OntologyRdf::RDF_TYPE,
    ];

    /** @var array<string, core_kernel_classes_Property> */
    private $sharedProperties = [];

    /** @var array<string, bool> */
    private $fileProperties = [];

    public function __construct(
        ClassMetadataMapperInterface $classMetadataMapper,
        FileReferenceSerializer $fileReferenceSerializer,
        FileSystemService $fileSystemService
    ) {
        $this->classMetadataMapper = $classMetadataMapper;
        $this->fileReferenceSerializer = $fileReferenceSerializer;
        $this->fileSystemService = $fileSystemService;
    }

    public function addPropertyUriToBlacklist(string $propertyUri): void
    {
        if (!in_array($propertyUri, $this->blacklistedProperties, true)) {
            $this->blacklistedProperties[] = $propertyUri;
        }
    }

    public function copy(
        core_kernel_classes_Resource $instance,
        core_kernel_classes_Resource $destinationInstance
    ): void {
        $originalClass = current($instance->getTypes());
        $destinationClass = current($destinationInstance->getTypes());

        $destinationProperties = $destinationClass->getProperties(true);
        $this->sharedProperties = array_merge(
            $this->sharedProperties,
            array_intersect_key($originalClass->getProperties(), $destinationProperties)
        );

        foreach ($destinationProperties as $destinationProperty) {
            $originalProperty = $this->getOriginalProperty($destinationProperty);

            if (
                $originalProperty === null
                || in_array($originalProperty->getUri(), $this->blacklistedProperties, true)
            ) {
                continue;
            }

            $propertyValues = $instance->getPropertyValuesCollection($originalProperty);

            /** @var core_kernel_classes_Literal|core_kernel_classes_Resource|core_kernel_classes_Resource[] $propertyValue */
            foreach ($propertyValues->getIterator() as $propertyValue) {
                if ($this->isFileProperty($originalProperty)) {
                    $this->copyFile($destinationInstance, $destinationProperty, $propertyValue);

                    continue;
                }

                $destinationInstance->setPropertyValue($destinationProperty, $propertyValue);
            }
        }
    }

    private function getOriginalProperty(core_kernel_classes_Property $property): ?core_kernel_classes_Property
    {
        if (array_key_exists($property->getUri(), $this->sharedProperties)) {
            return $property;
        }

        $originalPropertyUri = $this->classMetadataMapper->get($property);

        if ($originalPropertyUri === null) {
            return null;
        }

        return $property->getProperty($originalPropertyUri);
    }

    private function isFileProperty(core_kernel_classes_Property $property): bool
    {
        $propertyUri = $property->getUri();

        if (!array_key_exists($propertyUri, $this->fileProperties)) {
            $range = $property->getRange();

            $this->fileProperties[$propertyUri] = $range !== null
                && $range->getUri() === GenerisRdf::CLASS_GENERIS_FILE;
        }

        return $this->fileProperties[$propertyUri];
    }

    /**
     * @param core_kernel_classes_Literal|core_kernel_classes_Resource $propertyValue
     */
    private function copyFile(
        core_kernel_classes_Resource $instance,
        core_kernel_classes_Property $property,
        $propertyValue
    ): void {
        $oldFile = $this->fileReferenceSerializer->unserializeFile($propertyValue->getUri());

        $newFile = $this->fileSystemService
            ->getDirectory($oldFile->getFileSystemId())
            ->getFile(helpers_File::createFileName($oldFile->getBasename()));
        $newFile->write($oldFile->readStream());
        $newFileUri = $this->fileReferenceSerializer->serialize($newFile);

        $instance->setPropertyValue($property, $instance->getResource($newFileUri));
    }
}
