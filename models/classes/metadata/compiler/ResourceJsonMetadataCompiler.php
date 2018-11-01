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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA ;
 */

namespace oat\tao\model\metadata\compiler;

use core_kernel_classes_Resource;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\export\JsonLdExport;

/**
 * Class ResourceJsonMetadataCompiler
 * @package oat\tao\model\metadata\compiler
 */
class ResourceJsonMetadataCompiler extends ConfigurableService implements ResourceMetadataCompilerInterface
{
    const SERVICE_ID = 'tao/ResourceJsonMetadataCompiler';

    /**
     * @param core_kernel_classes_Resource $resource
     * @return array|mixed
     */
    public function compile(core_kernel_classes_Resource $resource)
    {
        $jsonExporter = new JsonLdExport($resource);

        return $jsonExporter->jsonSerialize();
    }
}
