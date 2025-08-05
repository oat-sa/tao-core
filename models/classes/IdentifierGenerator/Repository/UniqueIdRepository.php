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
 * Copyright (c) 2025 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\IdentifierGenerator\Repository;

use common_persistence_Persistence;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Query\QueryBuilder;
use InvalidArgumentException;
use oat\generis\persistence\PersistenceManager;
use oat\oatbox\log\LoggerService;

class UniqueIdRepository
{
    public const TABLE_NAME = 'unique_ids';
    public const FIELD_RESOURCE_ID = 'resource_id';
    public const FIELD_RESOURCE_TYPE = 'resource_type';
    public const FIELD_UNIQUE_ID = 'unique_id';
    public const FIELD_CREATED_AT = 'created_at';

    public function __construct(
        private readonly PersistenceManager $persistenceManager,
        private readonly LoggerService $logger,
        private readonly string $persistenceId = 'default'
    ) {
    }

    public function findOneBy(array $criteria, array $orderBy = []): ?array
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->select('*')->from(self::TABLE_NAME);

        foreach ($criteria as $field => $value) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq($field, ':' . $field))
                ->setParameter($field, $value);
        }

        foreach ($orderBy as $field => $direction) {
            $queryBuilder->addOrderBy($field, $direction);
        }

        $queryBuilder->setMaxResults(1);

        try {
            $result = $queryBuilder->execute()->fetchAssociative();
            return $result ?: null;
        } catch (Exception $e) {
            $this->logger->warning('Failed to find unique ID record', [
                'criteria' => $criteria,
                'orderBy' => $orderBy,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function save(array $data): void
    {
        if (!isset($data[self::FIELD_RESOURCE_TYPE], $data[self::FIELD_UNIQUE_ID], $data[self::FIELD_RESOURCE_ID])) {
            throw new InvalidArgumentException('resourceType, uniqueId, and resourceId are all required');
        }

        $queryBuilder = $this->getQueryBuilder();
        $platform = $this->getPersistence()->getPlatForm();

        try {
            $platform->beginTransaction();

            $queryBuilder->insert(self::TABLE_NAME)
                ->values([
                    self::FIELD_RESOURCE_TYPE => ':resourceType',
                    self::FIELD_UNIQUE_ID => ':uniqueId',
                    self::FIELD_RESOURCE_ID => ':resourceId',
                    self::FIELD_CREATED_AT => ':createdAt'
                ])
                ->setParameters([
                    'resourceType' => $data[self::FIELD_RESOURCE_TYPE],
                    'uniqueId' => $data[self::FIELD_UNIQUE_ID],
                    'resourceId' => $data[self::FIELD_RESOURCE_ID],
                    'createdAt' => date('Y-m-d H:i:s')
                ]);

            $queryBuilder->execute();
            $platform->commit();
        } catch (Exception $e) {
            $platform->rollback();
            $this->logger->warning('Failed to save unique ID record', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function getPersistence(): common_persistence_Persistence
    {
        return $this->persistenceManager->getPersistenceById($this->persistenceId);
    }

    private function getQueryBuilder(): QueryBuilder
    {
        return $this->getPersistence()->getPlatForm()->getQueryBuilder();
    }
}
