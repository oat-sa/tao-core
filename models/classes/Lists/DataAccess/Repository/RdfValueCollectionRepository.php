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
 * Copyright (c) 2020-2022 (original work) Open Assessment Technologies SA;
 *
 * @author Sergei Mikhailov <sergei.mikhailov@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\model\Lists\DataAccess\Repository;

use common_exception_Error;
use core_kernel_classes_Class as KernelClass;
use core_kernel_classes_Property as KernelProperty;
use core_kernel_classes_Resource as KernelResource;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\OntologyRdf;
use oat\generis\model\OntologyRdfs;
use oat\generis\persistence\PersistenceManager;
use oat\search\base\QueryBuilderInterface;
use oat\search\base\QueryCriterionInterface;
use oat\search\base\QueryInterface;
use oat\tao\model\Lists\Business\Contract\ValueCollectionRepositoryInterface;
use oat\tao\model\Lists\Business\Domain\CollectionType;
use oat\tao\model\Lists\Business\Domain\Value;
use oat\tao\model\Lists\Business\Domain\ValueCollection;
use oat\tao\model\Lists\Business\Domain\ValueCollectionSearchRequest;
use oat\tao\model\service\InjectionAwareService;

class RdfValueCollectionRepository extends InjectionAwareService implements ValueCollectionRepositoryInterface
{
    use OntologyAwareTrait;

    public const SERVICE_ID = 'tao/ValueCollectionRepository';

    /** @var PersistenceManager */
    private $persistenceManager;

    /** @var string */
    private $persistenceId;

    public function __construct(PersistenceManager $persistenceManager, string $persistenceId)
    {
        parent::__construct();

        $this->persistenceManager = $persistenceManager;
        $this->persistenceId = $persistenceId;
    }

    public function isApplicable(string $collectionUri): bool
    {
        return CollectionType::fromCollectionUri($collectionUri)->equals(CollectionType::default());
    }

    public function findAll(ValueCollectionSearchRequest $searchRequest): ValueCollection
    {
        /** @var ComplexSearchService $search */
        $search = $this->getModel()->getSearchInterface();
        $queryBuilder = $search->query();

        $query = $this->getQuery($search, $queryBuilder, $searchRequest);
        $this->enrichWithLimit($searchRequest, $queryBuilder);
        $this->enrichQueryWithValueCollectionSearchCondition($searchRequest, $query);
        $this->enrichQueryWithSubject($searchRequest, $query);
        $this->enrichQueryWithExcludedValueUris($searchRequest, $query);
        $this->enrichQueryWithObjects($searchRequest, $query);
        $this->enrichQueryWithOrderBy($queryBuilder);

        $values = [];
        $data = $search->getGateway()->searchTriples($queryBuilder, OntologyRdfs::RDFS_LABEL);
        foreach ($data as $triple) {
            $values[] = new Value(
                $triple->id,
                $triple->subject,
                $triple->object
            );
        }

        if ($searchRequest->hasValueCollectionUri()) {
            $valueCollectionUri = $searchRequest->getValueCollectionUri();
        } else {
            $valueCollectionUri = null;
        }

        return new ValueCollection($valueCollectionUri, ...$values);
    }

    public function persist(ValueCollection $valueCollection): bool
    {
        if ($valueCollection->hasDuplicates()) {
            throw new ValueConflictException("Value Collection {$valueCollection->getUri()} has duplicate values.");
        }

        $persistValueCollectionAction = function () use ($valueCollection): void {
            foreach ($valueCollection as $value) {
                $this->verifyUriUniqueness($value);

                if (null === $value->getId()) {
                    $this->insert($valueCollection, $value);
                } else {
                    $this->update($value);
                }
            }
        };

        try {
            $platform = $this->getPersistence();
            if ($platform instanceof \common_persistence_Transactional) {
                $platform->transactional($persistValueCollectionAction);
            } else {
                $persistValueCollectionAction();
            }
            return true;
        } catch (ValueConflictException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            return false;
        }
    }

    /**
     * @param string $valueCollectionUri
     *
     * @throws common_exception_Error
     */
    public function delete(string $valueCollectionUri): void
    {
        $listClass = new KernelClass($valueCollectionUri);

        $listItems = $listClass->getInstances(false);

        foreach ($listItems as $listItem) {
            $listItem->delete();
        }
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * @param Value $value
     *
     * @throws ValueConflictException
     */
    protected function verifyUriUniqueness(Value $value): void
    {
        if (!$value->hasModifiedUri()) {
            return;
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        if ((new KernelResource($value->getUri()))->exists() || (new KernelClass($value->getUri()))->exists()) {
            throw new ValueConflictException("Value with {$value->getUri()} is already defined");
        }
    }

    protected function insert(ValueCollection $valueCollection, Value $value): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $valueCollectionResource = new KernelClass($valueCollection->getUri());

        $valueCollectionResource->createInstance($value->getLabel(), '', $value->getUri());
    }

    private function update(Value $value): void
    {
        if (!$value->hasChanges()) {
            return;
        }

        $listValue = new KernelClass($value->getOriginalUri());

        $listValue->setLabel($value->getLabel());
        if ($value->hasModifiedUri()) {
            $listValue->updateUri($value->getUri());
        }
    }

    private function enrichQueryWithOrderBy(QueryBuilderInterface $query): void
    {
        $query->sort([OntologyRdfs::RDFS_LABEL => 'asc']);
    }

    private function enrichWithLimit(ValueCollectionSearchRequest $searchRequest, QueryBuilderInterface $query): void
    {
        if ($searchRequest->hasOffset()) {
            $query->setOffset($searchRequest->getOffset());
        }

        if ($searchRequest->hasLimit()) {
            $query->setLimit($searchRequest->getLimit());
        }
    }

    private function enrichQueryWithValueCollectionSearchCondition(
        ValueCollectionSearchRequest $searchRequest,
        QueryInterface $query
    ): void {
        $typeList = [];

        if ($searchRequest->hasPropertyUri()) {
            $rangeProperty = new KernelProperty(OntologyRdfs::RDFS_RANGE);
            $searchProperty = new KernelProperty($searchRequest->getPropertyUri());
            $typeList = $searchProperty->getPropertyValues($rangeProperty);
        }

        if ($searchRequest->hasValueCollectionUri()) {
            $typeList[] = $searchRequest->getValueCollectionUri();
        }

        if (!empty($typeList)) {
            $query->add(OntologyRdf::RDF_TYPE)->in(array_unique($typeList));
        }
    }

    private function enrichQueryWithSubject(ValueCollectionSearchRequest $searchRequest, QueryInterface $query): void
    {
        if (!$searchRequest->hasSubject()) {
            return;
        }

        $query->add(OntologyRdfs::RDFS_LABEL)
            ->contains($searchRequest->getSubject());
    }

    private function enrichQueryWithExcludedValueUris(
        ValueCollectionSearchRequest $searchRequest,
        QueryInterface $query
    ): void {
        if (!$searchRequest->hasExcluded()) {
            return;
        }

        $query->add(QueryCriterionInterface::VIRTUAL_URI_FIELD)
            ->notIn($searchRequest->getExcluded());
    }

    private function enrichQueryWithObjects(
        ValueCollectionSearchRequest $searchRequest,
        QueryInterface $query
    ): void {
        if (!$searchRequest->hasUris()) {
            return;
        }

        $query->add(QueryCriterionInterface::VIRTUAL_URI_FIELD)
            ->in($searchRequest->getUris());
    }

    private function getPersistence(): \common_persistence_Persistence
    {
        $ontologyOptions = $this->getModel()->getOptions();
        return $this->persistenceManager->getPersistenceById($ontologyOptions['persistence']);
    }

    public function count(ValueCollectionSearchRequest $searchRequest): int
    {
        /** @var ComplexSearchService $search */
        $search = $this->getModel()->getSearchInterface();
        $queryBuilder = $search->query();

        $query = $this->getQuery($search, $queryBuilder, $searchRequest);
        $this->enrichQueryWithValueCollectionSearchCondition($searchRequest, $query);

        return $search->getGateway()->count($queryBuilder);
    }

    private function getQuery(
        ComplexSearchService $search,
        QueryBuilderInterface $queryBuilder,
        ValueCollectionSearchRequest $searchRequest
    ): QueryInterface {
        $search->setLanguage(
            $queryBuilder,
            $searchRequest->getDataLanguage(),
            $searchRequest->getDefaultLanguage()
        );

        $query = $queryBuilder->newQuery();
        $queryBuilder->setCriteria($query);

        return $query;
    }
}
