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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA ;
 */

declare(strict_types=1);

namespace oat\tao\model\metadata\compiler;

use core_kernel_classes_Resource;
use oat\generis\model\GenerisRdf;
use oat\tao\model\export\JsonLdExport;
use oat\tao\model\export\Metadata\JsonLd\JsonLdTripleEncoderInterface;

class AdvancedJsonResourceMetadataCompiler implements ResourceMetadataCompilerInterface
{
    /** @var JsonLdTripleEncoderInterface */
    private $jsonLdTripleEncoder;

    /** @var JsonLdExport */
    private $jsonLdExport;

    public function __construct(
        JsonLdTripleEncoderInterface $jsonLdTripleEncoder,
        JsonLdExport $jsonLdExport
    ) {
        $this->jsonLdTripleEncoder = $jsonLdTripleEncoder;
        $this->jsonLdExport = $jsonLdExport;
    }

    /**
     * @inheritDoc
     */
    public function compile(core_kernel_classes_Resource $resource)
    {
        $data = $this->jsonLdExport
            ->setResource($resource)
            ->addTripleEncoder($this->jsonLdTripleEncoder)
            ->jsonSerialize();

        $data['@context']->type = JsonLdTripleEncoderInterface::RDF_TYPE;
        $data['@context']->value = JsonLdTripleEncoderInterface::RDF_VALUE;
        $data['@context']->alias = GenerisRdf::PROPERTY_ALIAS;

        return $data;
    }
}
