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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\form\DataProvider;

use core_kernel_persistence_starsql_StarModel;
use oat\generis\model\data\Ontology;

class ProxyFormDataProvider
{
    private Ontology $persistence;
    private BulkFormDataProvider $bulkFormDataProvider;
    private OntologyFormDataProvider $ontologyFormDataProvider;

    public function __construct(
        Ontology $persistence,
        BulkFormDataProvider $bulkFormDataProvider,
        OntologyFormDataProvider $ontologyFormDataProvider
    ) {
        $this->persistence = $persistence;
        $this->bulkFormDataProvider = $bulkFormDataProvider;
        $this->ontologyFormDataProvider = $ontologyFormDataProvider;
    }

    public function getProvider(): FormDataProviderInterface
    {
        if ($this->persistence instanceof core_kernel_persistence_starsql_StarModel) {
            return $this->bulkFormDataProvider;
        }

        return $this->ontologyFormDataProvider;
    }
}
