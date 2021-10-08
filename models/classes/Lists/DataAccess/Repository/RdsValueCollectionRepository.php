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
 * Copyright (c) 2020-2021 (original work) Open Assessment Technologies SA;
 *
 * @author Sergei Mikhailov <sergei.mikhailov@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\model\Lists\DataAccess\Repository;

use Throwable;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use oat\generis\model\OntologyAwareTrait;
use core_kernel_classes_ContainerCollection;
use core_kernel_classes_Class as KernelClass;
use oat\tao\model\Lists\Business\Domain\Value;
use oat\generis\persistence\PersistenceManager;
use oat\tao\model\service\InjectionAwareService;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use core_kernel_classes_Resource as KernelResource;
use oat\tao\model\Lists\Business\Domain\CollectionType;
use common_persistence_SqlPersistence as SqlPersistence;
use oat\tao\model\Lists\Business\Domain\ValueCollection;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use oat\tao\model\Lists\Business\Domain\ValueCollectionSearchRequest;
use oat\tao\model\Lists\Business\Contract\DependencyRepositoryInterface;
use oat\tao\model\Lists\Business\Contract\ValueCollectionRepositoryInterface;

class RdsValueCollectionRepository extends InjectionAwareService implements ValueCollectionRepositoryInterface
{
    use OntologyAwareTrait;

    public const SERVICE_ID = 'tao/RdsValueCollectionRepository';

    public const TABLE_LIST_ITEMS = 'list_items';

    public const FIELD_ITEM_LABEL = 'label';
    public const FIELD_ITEM_ID = 'id';
    public const FIELD_ITEM_URI = 'uri';
    public const FIELD_ITEM_LIST_URI = 'list_uri';

    public const TABLE_LIST_ITEMS_DEPENDENCIES = 'list_items_dependencies';
    public const FIELD_LIST_ITEM_ID = 'list_item_id';
    public const FIELD_LIST_ITEM_FIELD = 'field';
    public const FIELD_LIST_ITEM_VALUE = 'value';

    /** @var PersistenceManager */
    private $persistenceManager;

    /** @var string */
    private $persistenceId;

    /** @var bool */
    private $isListsDependencyEnabled;

    public function __construct(PersistenceManager $persistenceManager, string $persistenceId)
    {
        parent::__construct();

        $this->persistenceManager = $persistenceManager;
        $this->persistenceId = $persistenceId;
    }

    public function isApplicable(string $collectionUri): bool
    {
        return CollectionType::fromCollectionUri($collectionUri)->equals(CollectionType::remote());
    }

    public function findAll(ValueCollectionSearchRequest $searchRequest): ValueCollection
    {
        $query = $this->getPersistence()->getPlatForm()->getQueryBuilder();

        $this->enrichQueryWithAllowedValues($searchRequest, $query);
        $this->enrichQueryWithInitialCondition($query);
        $this->enrichQueryWithSelect($searchRequest, $query);
        $this->enrichQueryWithValueCollectionSearchCondition($searchRequest, $query);
        $this->enrichQueryWithSubject($searchRequest, $query);
        $this->enrichQueryWithExcludedValueUris($searchRequest, $query);
        $this->enrichQueryWithFilterValueUris($searchRequest, $query);
        $this->enrichQueryWithOrderById($query);

        $values = [];

        foreach ($query->execute()->fetchAll() as $rawValue) {
            $value = new Value(
                (int) $rawValue[self::FIELD_ITEM_ID],
                $rawValue[self::FIELD_ITEM_URI],
                $rawValue[self::FIELD_ITEM_LABEL]
            );
            $value->setListUri($rawValue[self::FIELD_ITEM_LIST_URI]);

            $values[] = $value;
        }

        $valueCollectionUri = $searchRequest->hasValueCollectionUri()
            ? $searchRequest->getValueCollectionUri()
            : $rawValue[self::FIELD_ITEM_LIST_URI] ?? null;

        return new ValueCollection($valueCollectionUri, ...$values);
    }

    public function persist(ValueCollection $valueCollection): bool
    {
        if ($valueCollection->hasDuplicates()) {
            throw new ValueConflictException("Value Collection {$valueCollection->getUri()} has duplicate values.");
        }

        foreach ($valueCollection as $value) {
            $this->verifyUriUniqueness($value);
        }

        $platform = $this->getPersistence()->getPlatForm();

        $platform->beginTransaction();

        try {
            $this->delete($valueCollection->getUri());

            foreach ($valueCollection as $value) {
                $this->insert($valueCollection, $value);
            }

            $platform->commit();

            return true;
        } catch (ValueConflictException $exception) {
            throw $exception;
        } catch (UniqueConstraintViolationException $exception) {
            throw new ValueConflictException(__('List item URI duplications found'));
        } catch (Throwable $exception) {
            return false;
        } finally {
            if (isset($exception)) {
                $platform->rollBack();
            }
        }
    }

    public function delete(string $valueCollectionUri): void
    {
        $query = $this->getPersistence()->getPlatForm()->getQueryBuilder();

        $this->deleteListItemsDependencies($query, $valueCollectionUri);

        $query->delete(self::TABLE_LIST_ITEMS)
            ->where($query->expr()->eq(self::FIELD_ITEM_LIST_URI, ':list_uri'))
            ->setParameter('list_uri', $valueCollectionUri)
            ->execute();
    }

    public function count(ValueCollectionSearchRequest $searchRequest): int
    {
        $query = $this->getPersistence()->getPlatForm()->getQueryBuilder();

        $this->enrichQueryWithInitialCondition($query);
        $this->enrichQueryWithSelect($searchRequest, $query);
        $this->enrichQueryWithValueCollectionSearchCondition($searchRequest, $query);

        return $query->execute()->rowCount();
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
        $platform = $this->getPersistence()->getPlatForm();
        $platform->beginTransaction();

        try {
            $qb = $platform->getQueryBuilder();
            $qb->insert(self::TABLE_LIST_ITEMS)
                ->values([
                    self::FIELD_ITEM_LABEL => ':label',
                    self::FIELD_ITEM_URI => ':uri',
                    self::FIELD_ITEM_LIST_URI => ':listUri',
                ])
                ->setParameters([
                    'uri' => $value->getUri(),
                    'label' => $value->getLabel(),
                    'listUri' => $valueCollection->getUri(),
                ])
                ->execute();

            $this->insertListItemsDependency($qb, $value);

            $platform->commit();
        } catch (Throwable $e) {
            $platform->rollBack();
        }
    }

    private function enrichQueryWithInitialCondition(QueryBuilder $query): void
    {
        $query->from(self::TABLE_LIST_ITEMS, 'items');
    }

    private function enrichQueryWithOrderById(QueryBuilder $query): void
    {
        $query->addOrderBy('items.' . self::FIELD_ITEM_ID);
    }

    private function enrichQueryWithSelect(ValueCollectionSearchRequest $searchRequest, QueryBuilder $query): void
    {
        $query
            ->select(
                'items.' . self::FIELD_ITEM_ID,
                'items.' . self::FIELD_ITEM_LIST_URI,
                'items.' . self::FIELD_ITEM_URI,
                'items.' . self::FIELD_ITEM_LABEL
            );

        if ($searchRequest->hasLimit()) {
            $query->setMaxResults($searchRequest->getLimit());
        }
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
            ->andWhere($expressionBuilder->eq(self::FIELD_ITEM_LIST_URI, ':collection_uri'))
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
                    sprintf('LOWER(%s)', self::FIELD_ITEM_LABEL),
                    ':label'
                )
            )
            ->setParameter('label', '%' . $searchRequest->getSubject() .'%');
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
                    self::FIELD_ITEM_LABEL,
                    ':excluded_value_uri'
                )
            )
            ->setParameter('excluded_value_uri', $searchRequest->getExcluded(), Connection::PARAM_STR_ARRAY);
    }

    private function enrichQueryWithFilterValueUris(
        ValueCollectionSearchRequest $searchRequest,
        QueryBuilder $query
    ): void {
        if (!$searchRequest->hasUris()) {
            return;
        }

        $expressionBuilder = $query->expr();

        $query
            ->andWhere($expressionBuilder->in(self::FIELD_ITEM_URI, ':uris'))
            ->setParameter('uris', $searchRequest->getUris(), Connection::PARAM_STR_ARRAY);
    }

    private function getPersistence(): SqlPersistence
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->persistenceManager->getPersistenceById($this->persistenceId);
    }

    private function deleteListItemsDependencies(QueryBuilder $query, string $valueCollectionUri): void
    {
        if ($this->isListsDependencyEnabled()) {
            $ids = $query->from(self::TABLE_LIST_ITEMS, 'items')
                ->select(self::FIELD_ITEM_ID)
                ->where($query->expr()->eq('items.' . self::FIELD_ITEM_LIST_URI, ':list_uri'))
                ->setParameter('list_uri', $valueCollectionUri)
                ->execute()
                ->fetchAll(FetchMode::COLUMN);

            if (!empty($ids)) {
                $query->delete(self::TABLE_LIST_ITEMS_DEPENDENCIES)
                    ->where($query->expr()->in(self::FIELD_LIST_ITEM_ID, ':list_items_ids'))
                    ->setParameter('list_items_ids', $ids, Connection::PARAM_STR_ARRAY)
                    ->execute();
            }
        }
    }

    private function insertListItemsDependency(QueryBuilder $qb, Value $value): void
    {
        if ($this->isListsDependencyEnabled() && $value->getDependencyUri() !== null) {
            $qb->insert(self::TABLE_LIST_ITEMS_DEPENDENCIES)
                ->values([
                    self::FIELD_LIST_ITEM_ID => ':list_item_id',
                    self::FIELD_LIST_ITEM_FIELD => ':field',
                    self::FIELD_LIST_ITEM_VALUE => ':value',
                ])
                ->setParameters([
                    'list_item_id' => $qb->getConnection()->lastInsertId('list_items_id_seq'),
                    'field' => 'uri',
                    'value' => $value->getDependencyUri(),
                ])
                ->execute();
        }
    }

    private function enrichQueryWithAllowedValues(ValueCollectionSearchRequest $request, QueryBuilder $query): void
    {
        if (!$request->hasParentListValues()) {
            return;
        }

        $parentList = $this->getParentList($request);

        if (!$parentList) {
            return;
        }

        $allowedItemIds = $this->getDependencyRepository()->findChildListIds(
            [
                'parentListUris' => [$parentList->getUri()],
                'parentListValues' => $request->getParentListValues(),
            ]
        );

        $query->andWhere($query->expr()->in(self::FIELD_ITEM_ID, ':allowed_item_ids'))
            ->setParameter('allowed_item_ids', $allowedItemIds, Connection::PARAM_STR_ARRAY);
    }

    /**
     * @return KernelClass|core_kernel_classes_ContainerCollection|null
     */
    private function getParentList(ValueCollectionSearchRequest $request)
    {
        if (!$request->hasPropertyUri()) {
            return null;
        }

        $parentProperty = $this->getProperty($request->getPropertyUri())->getDependsOnPropertyCollection()->current();

        return $parentProperty ? $parentProperty->getRange() : null;
    }

    private function isListsDependencyEnabled(): bool
    {
        if (!isset($this->isListsDependencyEnabled)) {
            $this->isListsDependencyEnabled = $this->getFeatureFlagChecker()->isEnabled(
                FeatureFlagCheckerInterface::FEATURE_FLAG_LISTS_DEPENDENCY_ENABLED
            );
        }

        return $this->isListsDependencyEnabled;
    }

    private function getFeatureFlagChecker(): FeatureFlagCheckerInterface
    {
        return $this->getServiceLocator()->get(FeatureFlagChecker::class);
    }

    private function getDependencyRepository(): DependencyRepositoryInterface
    {
        return $this->getServiceLocator()->getContainer()->get(DependencyRepository::class);
    }
}
