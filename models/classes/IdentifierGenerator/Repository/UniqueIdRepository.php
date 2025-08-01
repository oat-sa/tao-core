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
use DateTime;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use oat\generis\persistence\PersistenceManager;

class UniqueIdRepository
{
    public const TABLE_UNIQUE_IDS = 'unique_ids';
    public const FIELD_RESOURCE_ID = 'resource_id';
    public const FIELD_RESOURCE_TYPE = 'resource_type';
    public const FIELD_UNIQUE_ID = 'unique_id';
    public const FIELD_CREATED_AT = 'created_at';

    public function __construct(
        private readonly PersistenceManager $persistenceManager,
        private readonly string $persistenceId = 'default'
    ) {
    }

    public function getLastIdForResourceType(string $resourceType): ?int
    {
        $queryBuilder = $this->getQueryBuilder();

        $result = $queryBuilder
            ->select('MAX(CAST(' . self::FIELD_UNIQUE_ID . ' AS BIGINT)) as last_id')
            ->from(self::TABLE_UNIQUE_IDS)
            ->where($queryBuilder->expr()->eq(self::FIELD_RESOURCE_TYPE, ':resourceType'))
            ->setParameter('resourceType', $resourceType)
            ->execute()
            ->fetch();

        return $result && $result['last_id'] ? (int)$result['last_id'] : null;
    }

    public function insertUniqueId(string $resourceType, string $uniqueId, string $resourceId = null): void
    {
        $persistence = $this->getPersistence();
        $platform = $persistence->getPlatForm();

        try {
            $platform->beginTransaction();

            $persistence->insert(self::TABLE_UNIQUE_IDS, [
                self::FIELD_RESOURCE_ID => $resourceId,
                self::FIELD_RESOURCE_TYPE => $resourceType,
                self::FIELD_UNIQUE_ID => $uniqueId,
                self::FIELD_CREATED_AT => (new DateTime())->format('Y-m-d H:i:s'),
            ]);

            $platform->commit();
        } catch (Exception $e) {
            if ($platform->isTransactionActive()) {
                $platform->rollBack();
            }
            throw $e;
        }
    }

    public function getStartId(): int
    {
        return (int)($_ENV['TAO_UNIQUE_ID_START'] ?? 100000000);
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
