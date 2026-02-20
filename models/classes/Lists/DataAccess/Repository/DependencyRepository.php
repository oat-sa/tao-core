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

use Doctrine\DBAL\Connection;
use InvalidArgumentException;
use Doctrine\DBAL\Query\QueryBuilder;
use oat\tao\model\Context\ContextInterface;
use oat\generis\persistence\PersistenceManager;
use oat\tao\model\Lists\Business\Domain\Dependency;
use oat\tao\model\Lists\Business\Domain\DependencyCollection;
use oat\tao\model\Lists\Business\Domain\DependencyRepositoryContext;
use oat\tao\model\Lists\Business\Contract\DependencyRepositoryInterface;

class DependencyRepository implements DependencyRepositoryInterface
{
    public const OPTION_PARENT_LIST_URIS = 'parentListUris';
    public const OPTION_PARENT_LIST_VALUES = 'parentListValues';
    public const OPTION_LIST_URI = 'listUri';
    public const OPTION_PARENT_LIST_URI = 'parentListUri';

    /** @var PersistenceManager */
    private $persistenceManager;

    public function __construct(PersistenceManager $persistenceManager)
    {
        $this->persistenceManager = $persistenceManager;
    }

    public function findChildListIds(array $options): array
    {
        if (empty($options[self::OPTION_PARENT_LIST_URIS]) || empty($options[self::OPTION_PARENT_LIST_VALUES])) {
            throw new InvalidArgumentException('Parameters (parentListUris, parentListValues) are required');
        }

        $query = $this->getQueryBuilder();
        $expressionBuilder = $query->expr();

        return $query
            ->select('list_item_id')
            ->from(RdsValueCollectionRepository::TABLE_LIST_ITEMS_DEPENDENCIES, 'dependencies')
            ->innerJoin(
                'dependencies',
                RdsValueCollectionRepository::TABLE_LIST_ITEMS,
                'items',
                $expressionBuilder->eq('dependencies.value', 'items.uri')
            )
            ->andWhere($expressionBuilder->in('items.list_uri', ':parent_list_uri'))
            ->andWhere($expressionBuilder->in('dependencies.value', ':parent_list_value'))
            ->setParameter(
                'parent_list_uri',
                $options[self::OPTION_PARENT_LIST_URIS],
                Connection::PARAM_STR_ARRAY
            )
            ->setParameter(
                'parent_list_value',
                $options[self::OPTION_PARENT_LIST_VALUES],
                Connection::PARAM_STR_ARRAY
            )
            ->executeQuery()
            ->fetchFirstColumn();
    }

    public function findAll(array $options): DependencyCollection
    {
        $remoteListUri = $options[self::OPTION_LIST_URI];

        $query = $this->getQueryBuilder();
        $expressionBuilder = $query->expr();

        $query
            ->select('value')
            ->from(RdsValueCollectionRepository::TABLE_LIST_ITEMS_DEPENDENCIES, 'dependencies')
            ->innerJoin(
                'dependencies',
                RdsValueCollectionRepository::TABLE_LIST_ITEMS,
                'items',
                $expressionBuilder->eq(
                    'dependencies.' . RdsValueCollectionRepository::FIELD_LIST_ITEM_ID,
                    'items.' . RdsValueCollectionRepository::FIELD_ITEM_ID
                )
            )
            ->andWhere($expressionBuilder->eq('items.list_uri', ':label_uri'))
            ->setParameter('label_uri', $remoteListUri);

        $collection = new DependencyCollection();

        foreach ($query->executeQuery()->fetchFirstColumn() as $column) {
            $collection->append(new Dependency($column));
        }

        return $collection;
    }

    public function findChildListUris(array $options): array
    {
        $query = $this->getQueryBuilder();
        $expressionBuilder = $query->expr();

        return $query
            ->select(RdsValueCollectionRepository::FIELD_ITEM_LIST_URI)
            ->from(RdsValueCollectionRepository::TABLE_LIST_ITEMS, 'items')
            ->where(
                $expressionBuilder->in(
                    'items.id',
                    $this->getQueryBuilder()
                        ->select('dependencies.' . RdsValueCollectionRepository::FIELD_LIST_ITEM_ID)
                        ->from(RdsValueCollectionRepository::TABLE_LIST_ITEMS_DEPENDENCIES, 'dependencies')
                        ->innerJoin(
                            'dependencies',
                            RdsValueCollectionRepository::TABLE_LIST_ITEMS,
                            'items',
                            $expressionBuilder->eq(
                                'dependencies.' . RdsValueCollectionRepository::FIELD_LIST_ITEM_VALUE,
                                'items.' . RdsValueCollectionRepository::FIELD_ITEM_URI
                            )
                        )
                        ->andWhere($expressionBuilder->eq('items.list_uri', ':list_uri'))
                        ->getSQL()
                )
            )
            ->setParameter('list_uri', $options[self::OPTION_PARENT_LIST_URI])
            ->groupBy(RdsValueCollectionRepository::FIELD_ITEM_LIST_URI)
            ->executeQuery()
            ->fetchFirstColumn();
    }

    /**
     * {@inheritdoc}
     */
    public function findChildListItemsUris(ContextInterface $context): array
    {
        $parentListUris = $context->getParameter(DependencyRepositoryContext::PARAM_LIST_URIS);
        $parentListValues = $context->getParameter(
            DependencyRepositoryContext::PARAM_DEPENDENCY_LIST_VALUES,
            []
        );

        $childListIds = $this->findChildListIds(
            [
                self::OPTION_PARENT_LIST_URIS => $parentListUris,
                self::OPTION_PARENT_LIST_VALUES => $parentListValues,
            ]
        );

        $query = $this->getQueryBuilder();
        $expressionBuilder = $query->expr();

        return $query
            ->select(RdsValueCollectionRepository::FIELD_ITEM_URI)
            ->from(RdsValueCollectionRepository::TABLE_LIST_ITEMS, 'items')
            ->where(
                $expressionBuilder->in(
                    'items.' . RdsValueCollectionRepository::FIELD_ITEM_ID,
                    ':child_list_ids'
                )
            )
            ->setParameter('child_list_ids', $childListIds, Connection::PARAM_STR_ARRAY)
            ->executeQuery()
            ->fetchFirstColumn();
    }

    private function getQueryBuilder(): QueryBuilder
    {
        return $this->persistenceManager
            ->getPersistenceById('default')
            ->getPlatform()
            ->getQueryBuilder();
    }
}
