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
 *
 */

namespace oat\tao\scripts\install;

use core_kernel_persistence_starsql_StarModel;
use oat\generis\model\data\Ontology;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\oatbox\extension\InstallAction;
use oat\oatbox\log\ColoredVerboseLogger;
use oat\oatbox\log\LoggerAwareTrait;
use Psr\Log\LogLevel;

class EnableGraphDatabase extends InstallAction
{
    use LoggerAwareTrait;

    public function __invoke($params)
    {
        $this->setLogger(new ColoredVerboseLogger(LogLevel::INFO));

        $persistenceId = $params[0];
        if (empty($persistenceId)) {
            throw new \common_Exception('Persistence id should be provided in script parameters');
        }

        $this->updateOntologyService($persistenceId);
        $this->updateComplexSearchService();
    }

    private function updateOntologyService($persistenceId)
    {
        $ontologyService = $this->getServiceManager()->get(Ontology::SERVICE_ID);
        $serviceOptions = $ontologyService->getOptions();
        $serviceOptions[core_kernel_persistence_starsql_StarModel::OPTION_PERSISTENCE] = $persistenceId;
        $this
            ->getServiceManager()
            ->register(Ontology::SERVICE_ID, new core_kernel_persistence_starsql_StarModel($serviceOptions));

        $this->logInfo(sprintf('"%s" persistence has been set for ontology service', $persistenceId));
    }

    private function updateComplexSearchService()
    {
        $complexSearchService = $this->getServiceManager()->get(ComplexSearchService::SERVICE_ID);
        $serviceOptions = $complexSearchService->getOptions();
        $serviceOptions['shared']['search.neo4j.serialyser'] = false;
        $serviceOptions['invokables']['search.driver.neo4j'] =
            '\\oat\\generis\\model\\kernel\\persistence\\starsql\\search\\Neo4jEscapeDriver';
        $serviceOptions['invokables']['search.neo4j.serialyser'] =
            '\\oat\\generis\\model\\kernel\\persistence\\starsql\\search\\QuerySerializer';
        $serviceOptions['invokables']['search.tao.gateway'] =
            '\\oat\\generis\\model\\kernel\\persistence\\starsql\\search\\GateWay';
        $this
            ->getServiceManager()
            ->register(ComplexSearchService::SERVICE_ID, new ComplexSearchService($serviceOptions));

        $this
            ->logInfo('ComplexSearch service has been updated to compliant with graph database');
    }
}
