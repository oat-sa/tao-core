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
use oat\generis\model\OntologyRdfs;
use Doctrine\DBAL\Query\QueryBuilder;
use oat\generis\persistence\PersistenceManager;
use oat\tao\model\StatisticalMetadata\Model\MetadataProperty;
use oat\tao\model\StatisticalMetadata\Contract\StatisticalMetadataRepositoryInterface;

class StatisticalMetadataRepository implements StatisticalMetadataRepositoryInterface
{
    public const FILTER_ALIASES = 'aliases';

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
        $expr = $queryBuilder->expr();

        return $queryBuilder
            ->select(
                [
                    'st.subject as uri',
                    'st.object as alias',
                    'domain.object as domain'
                ]
            )
            ->from('statements', 'st')
            ->leftJoin(
                'st',
                'statements',
                'domain',
                $expr->eq('domain.subject', 'st.subject')
            )
            ->where('st.predicate = :propertyAlias')
            ->andWhere($expr->in('st.object', ':aliases'))
            ->andWhere('domain.predicate = :domain')
            ->setParameter('propertyAlias', GenerisRdf::PROPERTY_ALIAS)
            ->setParameter('aliases', $filters[self::FILTER_ALIASES], Connection::PARAM_STR_ARRAY)
            ->setParameter('domain', OntologyRdfs::RDFS_DOMAIN)
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
