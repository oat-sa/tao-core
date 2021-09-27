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

use core_kernel_classes_Property;
use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyRdf;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\Context\ContextInterface;
use oat\search\helper\SupportedOperatorHelper;
use oat\tao\model\Lists\Business\Domain\DependentPropertiesRepositoryContext;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\tao\model\Lists\Business\Contract\DependentPropertiesRepositoryInterface;

class DependentPropertiesRepository extends ConfigurableService implements DependentPropertiesRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findAll(ContextInterface $context): array
    {
        /** @var core_kernel_classes_Property $property */
        $property = $context->getParameter(DependentPropertiesRepositoryContext::PARAM_PROPERTY);

        $search = $this->getComplexSearchService();
        $dependentPropertiesQueryBuilder = $search->query();
        $dependentPropertiesQuery = $search->searchType(
            $dependentPropertiesQueryBuilder,
            OntologyRdf::RDF_PROPERTY,
            true
        );
        $dependentPropertiesQuery->addCriterion(
            GenerisRdf::PROPERTY_DEPENDS_ON_PROPERTY,
            SupportedOperatorHelper::EQUAL,
            $property->getUri()
        );
        $dependentPropertiesQueryBuilder->setCriteria($dependentPropertiesQuery);

        return iterator_to_array($search->getGateway()->search($dependentPropertiesQueryBuilder));
    }

    private function getComplexSearchService(): ComplexSearchService
    {
        return $this->getServiceLocator()->get(ComplexSearchService::SERVICE_ID);
    }
}
