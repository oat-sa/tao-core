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
 * Copyright (c) 2018 Open Assessment Technologies SA
 *
 */

namespace oat\tao\scripts\install;

use oat\generis\model\OntologyRdfs;
use oat\oatbox\extension\InstallAction;
use oat\tao\model\search\dataProviders\DataProvider;
use oat\tao\model\search\dataProviders\OntologyDataProvider;
use oat\tao\model\search\dataProviders\SearchDataProvider;
use oat\tao\model\TaoOntology;

class SetupSearchService extends InstallAction
{
    public function __invoke($params)
    {
        $options = [
            DataProvider::INDEXES_MAP_OPTION => [
                TaoOntology::CLASS_URI_ITEM => [
                    DataProvider::FIELDS_OPTION => [
                        'label'
                    ],
                ],
                TaoOntology::CLASS_URI_TEST => [
                    DataProvider::FIELDS_OPTION => [
                        'label'
                    ],
                ],
                TaoOntology::CLASS_URI_SUBJECT => [
                    DataProvider::FIELDS_OPTION => [
                        'label'
                    ],
                ],
                TaoOntology::CLASS_URI_GROUP => [
                    DataProvider::FIELDS_OPTION => [
                        'label'
                    ],
                ]
            ]
        ];
        $ontologyDataProvider = new OntologyDataProvider($options);
        $this->getServiceManager()->register(OntologyDataProvider::SERVICE_ID, $ontologyDataProvider);

        $searchDataProvider = new SearchDataProvider([SearchDataProvider::PROVIDERS_OPTION => [OntologyDataProvider::SERVICE_ID]]);
        $this->getServiceManager()->register(SearchDataProvider::SERVICE_ID, $searchDataProvider);
    }

}