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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\Lists\DataAccess\Repository;

use InvalidArgumentException;
use oat\generis\model\OntologyRdf;
use oat\generis\model\OntologyRdfs;
use oat\oatbox\service\ConfigurableService;
use oat\search\helper\SupportedOperatorHelper;
use oat\tao\model\Lists\Business\Domain\ValueCollectionSearchRequest;
use oat\tao\model\Lists\Business\Contract\DependencyRepositoryInterface;
use oat\tao\model\Lists\Business\Contract\ValueCollectionRepositoryInterface;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\tao\model\Lists\Business\Contract\ParentPropertyListRepositoryInterface;

class ParentPropertyListRepository extends ConfigurableService implements ParentPropertyListRepositoryInterface
{
    public function findAllUris(array $options): array
    {
        if (empty($options['listUri'])) {
            throw new InvalidArgumentException('listUri must be provided as a filter');
        }

        $dependencies = $this->getDependencyRepository()->findAll(
            [
                'listUri' => $options['listUri'],
            ]
        )->getValues();

        if (empty($dependencies)) {
            return [];
        }

        $dependencyListUris = $this->getRdsValueCollectionRepository()
            ->findAll((new ValueCollectionSearchRequest())->setUris(...$dependencies))
            ->getListUris();

        if (empty($dependencyListUris)) {
            return [];
        }

        $propertyList = [];

        $search = $this->getComplexSearchService();
        $propertyListQueryBuilder = $search->query();
        $propertyListQuery = $search->searchType($propertyListQueryBuilder, OntologyRdf::RDF_PROPERTY, true);
        $propertyListQuery->addCriterion(OntologyRdfs::RDFS_RANGE, SupportedOperatorHelper::IN, $dependencyListUris);
        $propertyListQueryBuilder->setCriteria($propertyListQuery);
        $propertyListResult = $search->getGateway()->search($propertyListQueryBuilder);

        foreach ($propertyListResult as $property) {
            $propertyList[] = $property->getUri();
        }

        return $propertyList;
    }

    private function getRdsValueCollectionRepository(): ValueCollectionRepositoryInterface
    {
        return $this->getServiceLocator()->get(RdsValueCollectionRepository::SERVICE_ID);
    }

    private function getDependencyRepository(): DependencyRepositoryInterface
    {
        return $this->getServiceLocator()->getContainer()->get(DependencyRepository::class);
    }

    private function getComplexSearchService(): ComplexSearchService
    {
        return $this->getServiceLocator()->get(ComplexSearchService::SERVICE_ID);
    }
}
