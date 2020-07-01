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

namespace oat\tao\model\Lists\Business\Service;

use oat\tao\model\Lists\Business\Contract\ValueCollectionRepositoryInterface;
use oat\tao\model\Lists\Business\Domain\ValueCollection;
use oat\tao\model\Lists\Business\Input\ValueCollectionSearchInput;
use oat\tao\model\Lists\DataAccess\Repository\ValueConflictException;
use oat\tao\model\service\InjectionAwareService;

class ValueCollectionService extends InjectionAwareService
{
    public const SERVICE_ID = 'tao/ValueCollectionService';

    /** @var ValueCollectionRepositoryInterface */
    private $repository;

    public function __construct(ValueCollectionRepositoryInterface $repository)
    {
        parent::__construct();

        $this->repository = $repository;
    }

    public function findAll(ValueCollectionSearchInput $input): ValueCollection
    {
        return $this->repository->findAll(
            $input->getSearchRequest()
        );
    }

    /**
     * @param ValueCollection $valueCollection
     *
     * @return bool
     *
     * @throws ValueConflictException
     */
    public function persist(ValueCollection $valueCollection): bool
    {
        // TODO Update linked property values in case a URI gets updated
        return $this->repository->persist($valueCollection);
    }
}
