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

use common_persistence_Persistence;
use core_kernel_classes_Property;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Connection;
use InvalidArgumentException;
use oat\generis\model\OntologyRdfs;
use oat\generis\persistence\PersistenceManager;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\Lists\Business\Contract\DependencyRepositoryInterface;
use oat\tao\model\Lists\Business\Contract\ParentPropertyListRepositoryInterface;
use oat\tao\model\Lists\Business\Contract\ValueCollectionRepositoryInterface;
use oat\tao\model\Lists\Business\Domain\ValueCollectionSearchRequest;

class ParentPropertyListRepository extends ConfigurableService implements ParentPropertyListRepositoryInterface
{
    public function findAllUris(array $options): array
    {
        /** @var core_kernel_classes_Property $property */
        $property = $options['property'] ?? null;

        $listUri = empty($options['listUri']) && $property
            ? $property->getRange()->getUri()
            : $options['listUri'];

        if (empty($listUri)) {
            throw new InvalidArgumentException('listUri must be provided as a filter');
        }

        $dependencies = $this->getDependencyRepository()->findAll(
            [
                'listUri' => $listUri
            ]
        )->getListUris();

        if (empty($dependencies)) {
            return [];
        }

        $dependencyListUris = $this->getRdsValueCollectionRepository()
            ->findAll((new ValueCollectionSearchRequest())->setUris(...$dependencies))
            ->getListUris();

        if (empty($dependencyListUris)) {
            return [];
        }

        //@FIXME @TODO Use complex search instead... @chinnu
        $query = $this->getQueryBuilder();
        $expressionBuilder = $query->expr();
        $query
            ->select('subject')
            ->from('statements')
            ->andWhere($expressionBuilder->eq('predicate', ':predicate'))
            ->andWhere($expressionBuilder->in('object', ':object'))
            ->setParameters(
                [
                    'predicate' => OntologyRdfs::RDFS_RANGE,
                    'object' => $dependencyListUris
                ],
                [
                    'object' => Connection::PARAM_STR_ARRAY
                ]
            );

        return $query->execute()->fetchAll(FetchMode::COLUMN);
    }

    private function getPersistence(): common_persistence_Persistence
    {
        return $this->getServiceManager()->get(PersistenceManager::SERVICE_ID)->getPersistenceById('default');
    }

    private function getQueryBuilder(): QueryBuilder
    {
        return $this->getPersistence()->getPlatform()->getQueryBuilder();
    }

    private function getRdsValueCollectionRepository(): ValueCollectionRepositoryInterface
    {
        return $this->getServiceLocator()->get(RdsValueCollectionRepository::SERVICE_ID);
    }

    private function getDependencyRepository(): DependencyRepositoryInterface
    {
        return $this->getServiceLocator()->get(DependencyRepository::class);
    }
}
