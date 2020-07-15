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

use common_persistence_SqlPersistence as SqlPersistence;
use core_kernel_classes_Class as KernelClass;
use core_kernel_classes_Resource as KernelResource;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use oat\generis\persistence\PersistenceManager;
use oat\tao\model\Lists\Business\Contract\ValueCollectionRepositoryInterface;
use oat\tao\model\Lists\Business\Domain\CollectionType;
use oat\tao\model\Lists\Business\Domain\Value;
use oat\tao\model\Lists\Business\Domain\ValueCollection;
use oat\tao\model\Lists\Business\Domain\ValueCollectionSearchRequest;
use oat\tao\model\service\InjectionAwareService;
use Throwable;

class RdsValueCollectionRepository extends InjectionAwareService implements ValueCollectionRepositoryInterface
{
    public const SERVICE_ID = 'tao/RdsValueCollectionRepository';

    public const TABLE_LIST_ITEMS = 'list_items';

    public const FIELD_ITEM_LABEL = 'label';
    public const FIELD_ITEM_ID = 'id';
    public const FIELD_ITEM_URI = 'uri';
    public const FIELD_ITEM_LIST_URI = 'list_uri';

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
        return CollectionType::fromCollectionUri($collectionUri)->equals(CollectionType::remote());
    }

    public function findAll(ValueCollectionSearchRequest $searchRequest): ValueCollection
    {
        $query = $this->getPersistence()->getPlatForm()->getQueryBuilder();

        $this->enrichQueryWithInitialCondition($query);
        $this->enrichQueryWithSelect($searchRequest, $query);
        $this->enrichQueryWithValueCollectionSearchCondition($searchRequest, $query);
        $this->enrichQueryWithSubject($searchRequest, $query);
        $this->enrichQueryWithExcludedValueUris($searchRequest, $query);
        $this->enrichQueryWithFilterValueUris($searchRequest, $query);

        $values = [];
        foreach ($query->execute()->fetchAll() as $rawValue) {
            $values[] = new Value(
                (int)$rawValue[self::FIELD_ITEM_ID],
                $rawValue[self::FIELD_ITEM_URI],
                $rawValue[self::FIELD_ITEM_LABEL]
            );
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

        $platform = $this->getPersistence()->getPlatForm();

        $platform->beginTransaction();

        try {
            foreach ($valueCollection as $value) {
//                $this->verifyUriUniqueness($value);

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

    public function delete(string $listUri): void
    {
        $query = $this->getPersistence()->getPlatForm()->getQueryBuilder();

        $query->delete(self::TABLE_LIST_ITEMS)
            ->where($query->expr()->eq(self::FIELD_ITEM_LIST_URI, ':list_uri'))
            ->setParameter('list_uri', $listUri)
            ->execute();
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
        $qb = $this->getPersistence()->getPlatForm()->getQueryBuilder();
        $qb->insert(self::TABLE_LIST_ITEMS)
            ->values(
                [
                    self::FIELD_ITEM_LABEL    => ':label',
                    self::FIELD_ITEM_URI      => ':uri',
                    self::FIELD_ITEM_LIST_URI => ':listUri',
                ]
            )
            ->setParameters(
                [
                    'uri'     => $value->getUri(),
                    'label'   => $value->getLabel(),
                    'listUri' => $valueCollection->getUri()
                ]
            )
            ->execute();
    }

    private function update(Value $value): void
    {
        if (!$value->hasChanges()) {
            return;
        }

        $query = $this->getPersistence()->getPlatForm()->getQueryBuilder();

        $expressionBuilder = $query->expr();

        $query
            ->update(self::TABLE_LIST_ITEMS)
            ->set(self::FIELD_ITEM_LABEL, ':label')
            ->set(self::FIELD_ITEM_URI, ':uri')
            ->where($expressionBuilder->eq('id', ':id'))
            ->setParameters(
                [
                    'id'    => $value->getId(),
                    'uri'   => $value->getUri(),
                    'label' => $value->getLabel(),
                ]
            )
            ->execute();

        $this->updateProperties($value);
    }

    /**
     * @param Value $value
     */
    private function updateProperties(Value $value): void
    {
        if (!$value->hasModifiedUri()) {
            return;
        }

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

    private function enrichQueryWithInitialCondition(QueryBuilder $query): void
    {
        $query->from(self::TABLE_LIST_ITEMS, 'items');
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
                $this->getPersistence()->getPlatForm()->getQueryBuilder()->expr()->like(self::FIELD_ITEM_LABEL, ':label')
            )
            ->setParameter('label', $searchRequest->getSubject() .'%');
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
    ): void
    {
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
}
