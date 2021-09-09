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

use common_persistence_SqlPersistence;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\FetchMode;
use oat\generis\persistence\PersistenceManager;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\Lists\Business\Contract\DependencyRepositoryInterface;
use oat\tao\model\Lists\Business\Domain\Dependency;
use oat\tao\model\Lists\Business\Domain\DependencyCollection;

class DependencyRepository extends ConfigurableService implements DependencyRepositoryInterface
{
    public function findAll(array $options): DependencyCollection
    {
        $remoteListUri = $options['listUri'];

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

        foreach ($query->execute()->fetchAll(FetchMode::COLUMN) as $colum) {
            $collection->append(new Dependency($colum));
        }

        return $collection;
    }

    private function getPersistence(): common_persistence_SqlPersistence
    {
        return $this->getServiceLocator()->get(PersistenceManager::SERVICE_ID)->getPersistenceById('default');
    }

    private function getQueryBuilder(): QueryBuilder
    {
        return $this->getPersistence()->getPlatform()->getQueryBuilder();
    }
}
