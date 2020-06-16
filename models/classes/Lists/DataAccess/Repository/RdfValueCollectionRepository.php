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
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use oat\generis\model\OntologyRdf;
use oat\generis\model\OntologyRdfs;
use oat\generis\persistence\PersistenceManager;
use oat\tao\model\service\InjectionAwareService;
use oat\tao\model\Lists\Business\Contract\ValueCollectionRepositoryInterface;
use oat\tao\model\Lists\Business\Domain\Value;
use oat\tao\model\Lists\Business\Domain\ValueCollection;
use oat\tao\model\Lists\Business\Domain\ValueCollectionSearchRequest;

class RdfValueCollectionRepository extends InjectionAwareService implements ValueCollectionRepositoryInterface
{
    /** @var PersistenceManager */
    private $persistenceManager;

    /** @var string */
    private $persistenceId;

    public function __construct(PersistenceManager $persistenceManager, string $persistenceId)
    {
        parent::__construct();

        $this->persistenceManager = $persistenceManager;
        $this->persistenceId      = $persistenceId;
    }

    public function findAll(ValueCollectionSearchRequest $searchRequest): ValueCollection
    {
        $query = $this->createInitialQuery($searchRequest);

        $this->enrichQueryWithSubject($searchRequest, $query);
        $this->enrichQueryWithExcludedValueUris($searchRequest, $query);

        $values = [];
        foreach ($query->execute()->fetchAll() as $rawValue) {
            $values[] = new Value($rawValue['subject'], $rawValue['object']);
        }

        return new ValueCollection(...$values);
    }

    private function createInitialQuery(ValueCollectionSearchRequest $searchRequest): QueryBuilder
    {
        $query = $this->getPersistence()->getPlatForm()->getQueryBuilder();

        $expressionBuilder = $query->expr();

        return $query
            ->select('filter.subject', 'filter.object')
            ->from('statements', 'filter')
            ->innerJoin(
                'filter',
                'statements',
                'collection',
                $expressionBuilder->eq('collection.subject', 'filter.subject')
            )
            ->innerJoin(
                'collection',
                'statements',
                'property',
                $expressionBuilder->eq('property.object', 'collection.object')
            )
            ->where($expressionBuilder->eq('property.subject', ':property_uri'))
            ->andWhere($expressionBuilder->eq('property.predicate', ':range_uri'))
            ->andWhere($expressionBuilder->eq('filter.predicate', ':label_uri'))
            ->andWhere($expressionBuilder->eq('collection.predicate', ':type_uri'))
            ->setParameters($this->createInitialQueryParameters($searchRequest))
            ->setMaxResults($searchRequest->getLimit());
    }

    private function enrichQueryWithSubject(ValueCollectionSearchRequest $searchRequest, QueryBuilder $query): void
    {
        if ($searchRequest->hasSubject()) {
            $query
                ->andWhere(
                    $this->getPersistence()->getPlatForm()->getQueryBuilder()->expr()->like('filter.object', ':subject')
                )
                ->setParameter('subject', "{$searchRequest->getSubject()}%");
        }
    }

    private function enrichQueryWithExcludedValueUris(
        ValueCollectionSearchRequest $searchRequest,
        QueryBuilder $query
    ): void {
        if ($searchRequest->hasExcluded()) {
            $query
                ->andWhere(
                    $this->getPersistence()->getPlatForm()->getQueryBuilder()->expr()->notIn(
                        'filter.subject',
                        ':excluded_value_uri'
                    )
                )
                ->setParameter('excluded_value_uri', $searchRequest->getExcluded(), Connection::PARAM_STR_ARRAY);
        }
    }

    private function createInitialQueryParameters(ValueCollectionSearchRequest $searchRequest): array
    {
        return [
            'property_uri' => $searchRequest->getPropertyUri(),
            'range_uri'    => OntologyRdfs::RDFS_RANGE,
            'label_uri'    => OntologyRdfs::RDFS_LABEL,
            'type_uri'     => OntologyRdf::RDF_TYPE,
        ];
    }

    private function getPersistence(): SqlPersistence
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->persistenceManager->getPersistenceById($this->persistenceId);
    }
}
