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
 */

declare(strict_types=1);

namespace oat\tao\model\StatisticalMetadata\Import\Extractor;

use core_kernel_classes_Class;
use oat\tao\model\TaoOntology;
use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\tao\model\StatisticalMetadata\Contract\Header;
use oat\tao\model\StatisticalMetadata\Import\Validator\RecordResourceValidator;
use oat\tao\model\StatisticalMetadata\Import\Exception\ErrorValidationException;

class ResourceExtractor
{
    /** @var RecordResourceValidator */
    private $recordResourceValidator;

    /** @var Ontology */
    private $ontology;

    /** @var array<string, core_kernel_classes_Class> */
    private $rootClassMap;

    public function __construct(RecordResourceValidator $recordResourceValidator, Ontology $ontology)
    {
        $this->recordResourceValidator = $recordResourceValidator;
        $this->ontology = $ontology;
    }

    /**
     * @throws ErrorValidationException
     */
    public function extract(array $record): core_kernel_classes_Resource
    {
        $this->recordResourceValidator->validateResourceId($record);

        $resourceHeader = $this->extractResourceHeader($record);
        $resource = $this->ontology->getResource($record[$resourceHeader]);

        try {
            $this->recordResourceValidator->validateResourceAvailability($resource);
            $this->recordResourceValidator->validateResourceType($resource, $this->getMappedRootClass($resourceHeader));
        } catch (ErrorValidationException $exception) {
            $exception->setColumn($resourceHeader);

            throw $exception;
        }

        return $resource;
    }

    private function extractResourceHeader(array $record): string
    {
        return empty($record[Header::ITEM_ID])
            ? Header::TEST_ID
            : Header::ITEM_ID;
    }

    private function getMappedRootClass(string $resourceHeader): core_kernel_classes_Class
    {
        if (!isset($this->rootClassMap)) {
            $this->rootClassMap = [
                Header::ITEM_ID => $this->ontology->getClass(TaoOntology::CLASS_URI_ITEM),
                Header::TEST_ID => $this->ontology->getClass(TaoOntology::CLASS_URI_TEST),
            ];
        }

        return $this->rootClassMap[$resourceHeader];
    }
}
