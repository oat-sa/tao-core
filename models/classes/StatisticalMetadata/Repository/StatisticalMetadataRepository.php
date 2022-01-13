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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\StatisticalMetadata\Repository;

use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Connection;
use InvalidArgumentException;
use oat\generis\model\GenerisRdf;
use Doctrine\DBAL\Query\QueryBuilder;
use oat\generis\persistence\PersistenceManager;
use oat\tao\model\StatisticalMetadata\Model\MetadataProperty;
use oat\tao\model\StatisticalMetadata\Contract\StatisticalMetadataRepositoryInterface;

class StatisticalMetadataRepository implements StatisticalMetadataRepositoryInterface
{
    public const FILTER_ALIASES = 'aliases';

    private const PROPERTY_ALIAS = GenerisRdf::PROPERTY_ALIAS;

    /** @var PersistenceManager */
    private $persistenceManager;

    public function __construct(PersistenceManager $persistenceManager)
    {
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * {@inheritdoc}
     */
    public function findProperties(array $filters): array
    {
        if (empty($filters[self::FILTER_ALIASES]) || !is_array($filters[self::FILTER_ALIASES])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Filter "%s" is not valid. It needs to be provided and must be an array.',
                    self::FILTER_ALIASES
                )
            );
        }

        $queryBuilder = $this->getQueryBuilder();

        return $queryBuilder
            ->select(
                [
                    'st.subject as uri',
                    'st.object as alias',
                ]
            )
            ->from('statements', 'st')
            ->where('st.predicate = :predicate')
            ->andWhere($queryBuilder->expr()->in('st.object', ':aliases'))
            ->setParameter('predicate', self::PROPERTY_ALIAS)
            ->setParameter('aliases', $filters[self::FILTER_ALIASES], Connection::PARAM_STR_ARRAY)
            ->execute()
            ->fetchAll(FetchMode::CUSTOM_OBJECT, MetadataProperty::class);
    }

    private function getQueryBuilder(): QueryBuilder
    {
        return $this->persistenceManager
            ->getPersistenceById('default')
            ->getPlatform()
            ->getQueryBuilder();
    }
}
