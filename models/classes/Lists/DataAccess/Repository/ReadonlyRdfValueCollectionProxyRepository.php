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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\Lists\DataAccess\Repository;

use oat\tao\model\Lists\Business\Contract\ValueCollectionRepositoryInterface;
use oat\tao\model\Lists\Business\Domain\CollectionType;
use oat\tao\model\Lists\Business\Domain\Value;
use oat\tao\model\Lists\Business\Domain\ValueCollection;
use oat\tao\model\Lists\Business\Domain\ValueCollectionSearchRequest;

class ReadonlyRdfValueCollectionProxyRepository implements ValueCollectionRepositoryInterface
{
    private ValueCollectionRepositoryInterface $rdfValueCollectionRepository;

    public function __construct(ValueCollectionRepositoryInterface $rdfValueCollectionRepository)
    {
        $this->rdfValueCollectionRepository = $rdfValueCollectionRepository;
    }

    public function findAll(ValueCollectionSearchRequest $searchRequest): ValueCollection
    {
        return $this->rdfValueCollectionRepository->findAll($searchRequest);
    }

    public function count(ValueCollectionSearchRequest $searchRequest): int
    {
        return $this->rdfValueCollectionRepository->count($searchRequest);
    }

    public function isApplicable(string $collectionUri): bool
    {
        return CollectionType::fromCollectionUri($collectionUri)->equals(CollectionType::readonly());
    }

    public function persist(ValueCollection $valueCollection): bool
    {
        throw new \LogicException('Readonly collections cannot be persisted');
    }

    public function delete(string $valueCollectionUri): void
    {
        throw new \LogicException('Readonly collections cannot be deleted');
    }

    protected function insert(ValueCollection $valueCollection, Value $value): void
    {
        throw new \LogicException('Readonly collections cannot be modified');
    }
}
