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
 * Copyright (c) 2020-2022 (original work) Open Assessment Technologies SA;
 *
 * @author Sergei Mikhailov <sergei.mikhailov@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\Lists\Business\Service;

use PHPUnit\Framework\MockObject\MockObject;
use oat\generis\test\ServiceManagerMockTrait;
use oat\oatbox\event\EventAggregator;
use oat\oatbox\session\SessionService;
use oat\oatbox\user\User;
use oat\oatbox\user\UserLanguageServiceInterface;
use oat\tao\model\Lists\Business\Contract\ValueCollectionRepositoryInterface;
use oat\tao\model\Lists\Business\Domain\ValueCollection;
use oat\tao\model\Lists\Business\Domain\ValueCollectionSearchRequest;
use oat\tao\model\Lists\Business\Input\ValueCollectionSearchInput;
use oat\tao\model\Lists\Business\Service\ValueCollectionService;
use PHPUnit\Framework\TestCase;

class ValueCollectionServiceTest extends TestCase
{
    use ServiceManagerMockTrait;

    /** @var ValueCollectionService */
    private $sut;

    /** @var ValueCollectionRepositoryInterface|MockObject */
    private $repositoryMock;

    /** @var EventAggregator|MockObject */
    private $eventAggregator;

    /**
     * @before
     */
    public function init(): void
    {
        $this->repositoryMock = $this->createMock(ValueCollectionRepositoryInterface::class);
        $this->eventAggregator = $this->createMock(EventAggregator::class);

        $this->sut = new ValueCollectionService(
            $this->repositoryMock
        );

        $sessionMock = $this->createMock(SessionService::class);
        $sessionMock->method('getCurrentUser')->willReturn($this->createMock(User::class));

        $userLanguageMock = $this->createMock(UserLanguageServiceInterface::class);
        $userLanguageMock->method('getDataLanguage')->willReturn('en-US');
        $userLanguageMock->method('getDefaultLanguage')->willReturn('en-US');

        $this->sut->setServiceManager(
            $this->getServiceManagerMock(
                [
                    SessionService::class => $sessionMock,
                    UserLanguageServiceInterface::SERVICE_ID => $userLanguageMock,
                    EventAggregator::SERVICE_ID => $this->eventAggregator,

                ]
            )
        );

        $this->eventAggregator
            ->expects($this->any())
            ->method('put');
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
