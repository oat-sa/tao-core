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

namespace oat\tao\test\unit\model\Lists\Business\Service;

use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\tao\model\Lists\Business\Contract\ValueCollectionRepositoryInterface;
use oat\tao\model\Lists\Business\Domain\ValueCollection;
use oat\tao\model\Lists\Business\Domain\ValueCollectionSearchRequest;
use oat\tao\model\Lists\Business\Input\ValueCollectionSearchInput;
use oat\tao\model\Lists\Business\Service\ValueCollectionService;

class ValueCollectionServiceTest extends TestCase
{
    /** @var ValueCollectionService */
    private $sut;

    /** @var ValueCollectionRepositoryInterface|MockObject */
    private $repositoryMock;

    /**
     * @before
     */
    public function init(): void
    {
        $this->repositoryMock = $this->createMock(ValueCollectionRepositoryInterface::class);

        $this->sut = new ValueCollectionService(
            $this->repositoryMock
        );
    }

    public function testFindAll(): void
    {
        $valueCollection = $this->createMock(ValueCollection::class);

        $this->assertSame(
            $valueCollection,
            $this->sut->findAll(
                $this->createSearchInputMock(
                    $this->createSearchRequestMock($valueCollection)
                )
            )
        );
    }

    private function createSearchInputMock(ValueCollectionSearchRequest $searchRequest): ValueCollectionSearchInput
    {
        $valueCollectionSearchInputMock = $this->createMock(ValueCollectionSearchInput::class);

        $valueCollectionSearchInputMock
            ->expects(static::once())
            ->method('getSearchRequest')
            ->willReturn($searchRequest);

        return $valueCollectionSearchInputMock;
    }

    private function createSearchRequestMock(ValueCollection $valueCollection): ValueCollectionSearchRequest
    {
        $valueCollectionSearchRequestMock = $this->createMock(ValueCollectionSearchRequest::class);

        $this->repositoryMock
            ->expects(static::once())
            ->method('findAll')
            ->with($valueCollectionSearchRequestMock)
            ->willReturn($valueCollection);

        return $valueCollectionSearchRequestMock;
    }
}
