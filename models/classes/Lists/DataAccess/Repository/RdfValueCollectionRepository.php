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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 *
 * @author Sergei Mikhailov <sergei.mikhailov@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\model\Lists\DataAccess\Repository;

use common_exception_Error;
use common_persistence_SqlPersistence as SqlPersistence;
use core_kernel_classes_Class as KernelClass;
use core_kernel_classes_Resource as KernelResource;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use oat\generis\model\OntologyRdf;
use oat\generis\model\OntologyRdfs;
use oat\generis\persistence\PersistenceManager;
use oat\tao\model\Lists\Business\Domain\CollectionType;
use oat\tao\model\service\InjectionAwareService;
use oat\tao\model\Lists\Business\Contract\ValueCollectionRepositoryInterface;
use oat\tao\model\Lists\Business\Domain\Value;
use oat\tao\model\Lists\Business\Domain\ValueCollection;
use oat\tao\model\Lists\Business\Domain\ValueCollectionSearchRequest;
use Throwable;

class RdfValueCollectionRepository extends InjectionAwareService implements ValueCollectionRepositoryInterface
{
    public const SERVICE_ID = 'tao/ValueCollectionRepository';

    /** @var PersistenceManager */
    private $persistenceManager;

    /** @var string */
    private $persistenceId;

    public function __construct(PersistenceManager $persistenceManager, string $persistenceId)
    {
        parent::__construct();

        $this->persistenceManager = $persistenceManager;
        $this->persistenceId      = $persistenceId;
    }

    public function isApplicable(string $collectionUri): bool
    {
        return CollectionType::fromCollectionUri($collectionUri)->equals(CollectionType::default());
    }

    public function findAll(ValueCollectionSearchRequest $searchRequest): ValueCollection
    {
        $query = $this->getPersistence()->getPlatForm()->getQueryBuilder();

        $this->enrichWithInitialCondition($query);
        $this->enrichWithSelect($searchRequest, $query);
        $this->enrichQueryWithPropertySearchConditions($searchRequest, $query);
        $this->enrichQueryWithValueCollectionSearchCondition($searchRequest, $query);
        $this->enrichQueryWithSubject($searchRequest, $query);
        $this->enrichQueryWithExcludedValueUris($searchRequest, $query);
        $this->enrichQueryWithObjects($searchRequest, $query);
        $this->enrichQueryWithOrderById($query);

        $values = [];
        foreach ($query->execute()->fetchAll() as $rawValue) {
            $values[] = new Value((int)$rawValue['id'], $rawValue['subject'], $rawValue['object']);
        }

        $valueCollectionUri = $searchRequest->hasValueCollectionUri()
            ? $searchRequest->getValueCollectionUri()
            : $rawValue['collection_uri'] ?? null;

        return new ValueCollection($valueCollectionUri, ...$values);
    }

    public function persist(ValueCollection $valueCollection): bool
    {
        if ($valueCollection->hasDuplicates()) {
            throw new ValueConflictException("Value Collection {$valueCollection->getUri()} has duplicate values.");
        }

        $platform = $this->getPersistence()->getPlatForm();

        $platform->beginTransaction();

        try {
            foreach ($valueCollection as $value) {
                $this->verifyUriUniqueness($value);

                if (null === $value->getId()) {
                    $this->insert($valueCollection, $value);
                } else {
                    $this->update($value);
                }
            }

            $platform->commit();

            return true;
        } catch (ValueConflictException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            return false;
        } finally {
            if (isset($exception)) {
                $platform->rollBack();
            }
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

        $query = $this->getPersistence()->getPlatForm()->getQueryBuilder();

        $expressionBuilder = $query->expr();

        $query
            ->update('statements')
            ->set('object', ':label')
            ->set('subject', ':uri')
            ->where($expressionBuilder->eq('id', ':id'))
            ->setParameters(
                [
                    'id'    => $value->getId(),
                    'uri'   => $value->getUri(),
                    'label' => $value->getLabel(),
                ]
            )
            ->execute();

        $this->updateRelations($value);
    }

    private function updateRelations(Value $value): void
    {
        if (!$value->hasModifiedUri()) {
            return;
        }

        $this->updateValues($value);
        $this->updateProperties($value);
    }

    /**
     * @param Value $value
     */
    private function updateValues(Value $value): void
    {
        $query = $this->getPersistence()->getPlatForm()->getQueryBuilder();

        $expressionBuilder = $query->expr();

        $query
            ->update('statements')
            ->set('subject', ':uri')
            ->where($expressionBuilder->eq('subject', ':original_uri'))
            ->setParameters(
                [
                    'uri'          => $value->getUri(),
                    'original_uri' => $value->getOriginalUri(),
                ]
            )
            ->execute();
    }

    /**
     * @param Value $value
     */
    private function updateProperties(Value $value): void
    {
        $query = $this->getPersistence()->getPlatForm()->getQueryBuilder();

        $expressionBuilder = $query->expr();

        $query
            ->update('statements')
            ->set('object', ':uri')
            ->where($expressionBuilder->eq('object', ':original_uri'))
            ->setParameters(
                [
                    'uri'          => $value->getUri(),
                    'original_uri' => $value->getOriginalUri(),
                ]
            )
            ->execute();
    }

    private function enrichWithInitialCondition(QueryBuilder $query): QueryBuilder
    {
        $expressionBuilder = $query->expr();

        $query
            ->from('statements', 'element')
            ->innerJoin(
                'element',
                'statements',
                'collection',
                $expressionBuilder->eq('collection.subject', 'element.subject')
            )
            ->andWhere($expressionBuilder->eq('element.predicate', ':label_uri'))
            ->andWhere($expressionBuilder->eq('collection.predicate', ':type_uri'))
            ->setParameters(
                [
                    'label_uri' => OntologyRdfs::RDFS_LABEL,
                    'type_uri'  => OntologyRdf::RDF_TYPE,
                ]
            );

        return $query;
    }

    private function enrichQueryWithOrderById(QueryBuilder $query): void
    {
        $query->addOrderBy('element.id');
    }

    private function enrichWithSelect(ValueCollectionSearchRequest $searchRequest, QueryBuilder $query): QueryBuilder
    {
        $query
            ->select('collection.object as collection_uri', 'element.id', 'element.subject', 'element.object');

        if ($searchRequest->hasLimit()) {
            $query->setMaxResults($searchRequest->getLimit());
        }

        return $query;
    }

    private function enrichQueryWithPropertySearchConditions(
        ValueCollectionSearchRequest $searchRequest,
        QueryBuilder $query
    ): void {
        if (!$searchRequest->hasPropertyUri()) {
            return;
        }

        $expressionBuilder = $query->expr();

        $query
            ->innerJoin(
                'collection',
                'statements',
                'property',
                $expressionBuilder->eq('property.object', 'collection.object')
            )
            ->andWhere($expressionBuilder->eq('property.subject', ':property_uri'))
            ->andWhere($expressionBuilder->eq('property.predicate', ':range_uri'))
            ->setParameter('property_uri', $searchRequest->getPropertyUri())
            ->setParameter('range_uri', OntologyRdfs::RDFS_RANGE);
    }

    private function enrichQueryWithValueCollectionSearchCondition(
        ValueCollectionSearchRequest $searchRequest,
        QueryBuilder $query
    ): void {
        if (!$searchRequest->hasValueCollectionUri()) {
            return;
        }

        $expressionBuilder = $query->expr();

        $query
            ->andWhere($expressionBuilder->eq('collection.object', ':collection_uri'))
            ->setParameter('collection_uri', $searchRequest->getValueCollectionUri());
    }

    private function enrichQueryWithSubject(ValueCollectionSearchRequest $searchRequest, QueryBuilder $query): void
    {
        if (!$searchRequest->hasSubject()) {
            return;
        }

        $query
            ->andWhere(
                $this->getPersistence()->getPlatForm()->getQueryBuilder()->expr()->like(
                    'LOWER(element.object)',
                    ':subject'
                )
            )
            ->setParameter('subject', $searchRequest->getSubject() . '%');
    }

    private function enrichQueryWithExcludedValueUris(
        ValueCollectionSearchRequest $searchRequest,
        QueryBuilder $query
    ): void {
        if (!$searchRequest->hasExcluded()) {
            return;
        }

        $query
            ->andWhere(
                $this->getPersistence()->getPlatForm()->getQueryBuilder()->expr()->notIn(
                    'element.subject',
                    ':excluded_value_uri'
                )
            )
            ->setParameter('excluded_value_uri', $searchRequest->getExcluded(), Connection::PARAM_STR_ARRAY);
    }

    private function enrichQueryWithObjects(
        ValueCollectionSearchRequest $searchRequest,
        QueryBuilder $query
    ): void {
        if (!$searchRequest->hasUris()) {
            return;
        }

        $expressionBuilder = $query->expr();

        $query
            ->andWhere($expressionBuilder->in('element.subject', ':subjects'))
            ->setParameter('subjects', $searchRequest->getUris(), Connection::PARAM_STR_ARRAY);
    }

    private function getPersistence(): SqlPersistence
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->persistenceManager->getPersistenceById($this->persistenceId);
    }
}
