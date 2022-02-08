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

namespace oat\tao\model\StatisticalMetadata\DataStore\Compiler;

use core_kernel_classes_Resource;
use oat\tao\model\metadata\compiler\ResourceMetadataCompilerInterface;
use oat\tao\model\StatisticalMetadata\Contract\Header;
use oat\tao\model\TaoOntology;

class StatisticalJsonResourceMetadataCompiler implements ResourceMetadataCompilerInterface
{
    /** @var ResourceMetadataCompilerInterface */
    private $resourceMetadataCompiler;

    /** @var array */
    private $aliases = [];

    public function __construct(ResourceMetadataCompilerInterface $jsonMetadataCompiler)
    {
        $this->resourceMetadataCompiler = $jsonMetadataCompiler;
    }

    public function withAliases(array $aliases): self
    {
        $this->aliases = $aliases;

        return $this;
    }

    public function compile(core_kernel_classes_Resource $resource): array
    {
        $compiled = $this->resourceMetadataCompiler->compile($resource);
        $compiled['@type'] = isset($record[Header::ITEM_ID])
            ? TaoOntology::CLASS_URI_ITEM
            : TaoOntology::CLASS_URI_TEST;

        foreach ($compiled as $key => $value) {
            if (strpos($key, '@') === 0) {
                continue;
            }

            if (!isset($value['alias'])) {
                unset($compiled[$key]);

                continue;
            }

            if (!empty($this->aliases) && !in_array($value['alias'], $this->aliases, true)) {
                unset($compiled[$key]);
            }
        }

        return $compiled;
    }
}
