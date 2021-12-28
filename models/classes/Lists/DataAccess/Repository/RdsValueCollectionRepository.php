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
use core_kernel_classes_Class;
use Doctrine\DBAL\Query\QueryBuilder;
use Psr\Container\ContainerInterface;
use common_persistence_SqlPersistence;
use oat\generis\model\OntologyAwareTrait;
use core_kernel_classes_ContainerCollection;
use oat\tao\model\Lists\Business\Domain\Value;
use oat\generis\persistence\PersistenceManager;
use oat\tao\model\service\InjectionAwareService;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\Lists\Business\Domain\CollectionType;
use oat\tao\model\Lists\Business\Domain\ValueCollection;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\oatbox\log\logger\extender\ContextExtenderInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use oat\tao\model\Lists\Business\Domain\ValueCollectionSearchRequest;
use oat\tao\model\Lists\Business\Contract\DependencyRepositoryInterface;
use oat\tao\model\Lists\Business\Contract\ValueCollectionRepositoryInterface;
use function Webmozart\Assert\Tests\StaticAnalysis\throws;

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
        $query = $this->getQueryBuilder();

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
        $this->verifyListElementsUniqueness($valueCollection);

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
            throw new ValueConflictException(
                sprintf(
                    'List "%s" has duplicated values. (%s)',
                    $valueCollection->getUri(),
                    $exception->getMessage()
                ),
                __('List "%s" has duplicated values.', $valueCollection->getUri())
            );
        } catch (Throwable $exception) {
            return false;
        } finally {
            if (isset($exception)) {
                $this->logError(
                    sprintf('List "%s" persistence failed', $valueCollection->getUri()),
                    [
                        ContextExtenderInterface::CONTEXT_EXCEPTION => $exception,
                    ]
                );

                $platform->rollBack();
            }
        }
    }

    public function delete(string $valueCollectionUri): void
    {
        $query = $this->getQueryBuilder();

        $this->deleteListItemsDependencies($query, $valueCollectionUri);

        $query->delete(self::TABLE_LIST_ITEMS)
            ->where($query->expr()->eq(self::FIELD_ITEM_LIST_URI, ':list_uri'))
            ->setParameter('list_uri', $valueCollectionUri)
            ->execute();
    }

    public function count(ValueCollectionSearchRequest $searchRequest): int
    {
        $query = $this->getQueryBuilder();

        $this->enrichQueryWithInitialCondition($query);
        $this->enrichQueryWithSelect($searchRequest, $query);
        $this->enrichQueryWithValueCollectionSearchCondition($searchRequest, $query);

        return $query->execute()->rowCount();
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
        } catch (Throwable $exception) {
            $this->logError(
                sprintf(
                    'Cannot persist list element "%s" ("%s") for list "%s". Exception: %s. Message: %s',
                    $value->getLabel(),
                    $value->getUri(),
                    $valueCollection->getUri(),
                    get_class($exception),
                    $exception->getMessage()
                )
            );

            $platform->rollBack();
        }
    }

    private function verifyListElementsUniqueness(ValueCollection $valueCollection): void
    {
        if ($valueCollection->hasDuplicates()) {
            $duplicatedValues = implode('", "', $valueCollection->getDuplicatedValues(5)->getUris());
            $valueConflictException = new ValueConflictException(
                sprintf(
                    'List "%s" has duplicated values: "%s"',
                    $valueCollection->getUri(),
                    $duplicatedValues
                ),
                __(
                    'List "%s" has duplicated values: "%s"',
                    $valueCollection->getUri(),
                    $duplicatedValues
                )
            );
            $this->logError($valueConflictException->getMessage());

            throw $valueConflictException;
        }

        $queryBuilder = $this->getQueryBuilder();
        $expr = $queryBuilder->expr();

        $existingUris = $queryBuilder
            ->select('items.' . self::FIELD_ITEM_URI)
            ->from(self::TABLE_LIST_ITEMS, 'items')
            ->where(
                $expr->neq(
                    'items.' . self::FIELD_ITEM_LIST_URI,
                    ':listUri'
                )
            )
            ->andWhere(
                $expr->in(
                    'items.' . self::FIELD_ITEM_URI,
                    ':uris'
                )
            )
            ->setParameter('listUri', $valueCollection->getUri())
            ->setParameter('uris', $valueCollection->getUris(), Connection::PARAM_STR_ARRAY)
            ->setMaxResults(5)
            ->execute()
            ->fetchAll(FetchMode::COLUMN);

        if (!empty($existingUris)) {
            $existingUrisList = implode('", "', $existingUris);
            $valueConflictException = new ValueConflictException(
                sprintf(
                    'List contains elements whose URIs are already defined: "%s"',
                    $existingUrisList
                ),
                __(
                    'List contains elements whose URIs are already defined: "%s"',
                    $existingUrisList
                )
            );
            $this->logError($valueConflictException->getMessage());

            throw $valueConflictException;
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
                $this->getQueryBuilder()->expr()->like(
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
                $this->getQueryBuilder()->expr()->notIn(
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

    private function getPersistence(): common_persistence_SqlPersistence
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
        if (!$this->isListsDependencyEnabled() || !$request->hasParentListValues()) {
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

        $query
            ->andWhere(
                $query->expr()->orX(
                    $query->expr()->in(self::FIELD_ITEM_ID, ':allowed_item_ids'),
                    $query->expr()->in(self::FIELD_ITEM_URI, ':allowed_item_uris')
                )
            )
            ->setParameter('allowed_item_ids', $allowedItemIds, Connection::PARAM_STR_ARRAY)
            ->setParameter('allowed_item_uris', $request->getSelectedValues(), Connection::PARAM_STR_ARRAY);
    }

    /**
     * @return core_kernel_classes_Class|core_kernel_classes_ContainerCollection|null
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
        return $this->getContainer()->get(FeatureFlagChecker::class);
    }

    private function getDependencyRepository(): DependencyRepositoryInterface
    {
        return $this->getContainer()->get(DependencyRepository::class);
    }

    private function getContainer(): ContainerInterface
    {
        return $this->getServiceLocator()->getContainer();
    }

    private function getQueryBuilder(): QueryBuilder
    {
        return $this->getPersistence()->getPlatForm()->getQueryBuilder();
    }
}
