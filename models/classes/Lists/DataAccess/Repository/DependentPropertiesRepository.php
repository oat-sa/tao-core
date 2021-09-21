<?php

declare(strict_types=1);

namespace oat\tao\model\Lists\DataAccess\Repository;

use core_kernel_classes_Property;
use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyRdf;
use oat\oatbox\service\ConfigurableService;
use oat\search\helper\SupportedOperatorHelper;
use oat\tao\model\Lists\Business\Domain\DependentPropertiesRepositoryContext;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\tao\model\Lists\Business\Contract\DependentPropertiesRepositoryInterface;

class DependentPropertiesRepository extends ConfigurableService implements DependentPropertiesRepositoryInterface
{
    /**
     * @return core_kernel_classes_Property[]
     */
    public function findAll(DependentPropertiesRepositoryContext $context): array
    {
        $dependentProperties = [];
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
        $result = $search->getGateway()->search($dependentPropertiesQueryBuilder);

        /** @var string $dependentPropertyUri */
        foreach ($result as $dependentProperty) {
            $dependentProperties[] = $dependentProperty;
        }

        return $dependentProperties;
    }

    private function getComplexSearchService(): ComplexSearchService
    {
        return $this->getServiceLocator()->get(ComplexSearchService::SERVICE_ID);
    }
}
