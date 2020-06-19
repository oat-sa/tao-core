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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\search\index\DocumentBuilder;

use oat\tao\model\search\index\IndexDocument;

interface IndexDocumentBuilderInterface
{
    const TYPE_PROPERTY = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type';

    /**
     * Creates IndexDocument object from the \core_kernel_classes_Resource data
     * @param \core_kernel_classes_Resource $resource
     * @param string $rootResourceType
     * @return IndexDocument
     * @throws \common_Exception
     * @throws \common_exception_InconsistentData
     */
    public function createDocumentFromResource(\core_kernel_classes_Resource $resource, string $rootResourceType = ""): IndexDocument;

    /**
     * Creates IndexDocument object from the array data
     * @param $resource $array
     * @param string $rootResourceType
     * @return IndexDocument
     * @throws \common_Exception
     */
    public function createDocumentFromArray(array $resource = [], string $rootResourceType = ""): IndexDocument;
    
    /**
     * Gets a list of dynamic properties for indexation
     * @param array $type
     * @param \core_kernel_classes_Resource $resource
     * @return \Iterator
     */
    public function getDynamicProperties(array $type, \core_kernel_classes_Resource $resource): \Iterator;
}

