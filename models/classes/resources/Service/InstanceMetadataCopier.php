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
use oat\oatbox\filesystem\File;
use core_kernel_classes_Resource;
use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyRdf;
use oat\generis\model\OntologyRdfs;
use oat\taoItems\model\TaoItemOntology;
use oat\oatbox\filesystem\FileSystemService;
use oat\generis\model\fileReference\FileReferenceSerializer;
use oat\tao\model\resources\Contract\InstanceMetadataCopierInterface;

class InstanceMetadataCopier implements InstanceMetadataCopierInterface
{
    /** @var ClassMetadataMapper */
    private $classMetadataMapper;

    /** @var FileReferenceSerializer */
    private $fileReferenceSerializer;

    /** @var FileSystemService */
    private $fileSystemService;

    /** @var string[] */
    private $blacklistedProperties = [
        OntologyRdf::RDF_TYPE,
    ];

    public function __construct(
        ClassMetadataMapper $classMetadataMapper,
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
        $destinationClass = current($destinationInstance->getTypes());

        foreach ($destinationClass->getProperties(true) as $destinationProperty) {
            $originalPropertyUri = $this->classMetadataMapper->get($destinationProperty->getUri());

            if (
                $originalPropertyUri === null
                || in_array($originalPropertyUri, $this->blacklistedProperties, true)
            ) {
                continue;
            }

            $originalProperty = $instance->getProperty($originalPropertyUri);
            $range = $originalProperty->getRange();
            $propertyValues = $instance->getPropertyValuesCollection($originalProperty);

            foreach ($propertyValues->getIterator() as $propertyValue) {
                if ($range === null || $range->getUri() !== GenerisRdf::CLASS_GENERIS_FILE) {
                    $destinationInstance->setPropertyValue($destinationProperty, $propertyValue);

                    continue;
                }

                $oldFile = $this->fileReferenceSerializer->unserializeFile($propertyValue->getUri());

                $newFile = $this->fileSystemService
                    ->getDirectory($oldFile->getFileSystemId())
                    ->getFile(helpers_File::createFileName($oldFile->getBasename()));
                $newFile->write($oldFile->readStream());
                $newFileUri = $this->fileReferenceSerializer->serialize($newFile);

                $destinationInstance->setPropertyValue(
                    $destinationProperty,
                    $destinationInstance->getResource($newFileUri)
                );
            }
        }
    }
}
