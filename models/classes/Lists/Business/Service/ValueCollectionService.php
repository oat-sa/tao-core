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
 * Copyright (c) 2020-2021 (original work) Open Assessment Technologies SA;
 *
 * @author Sergei Mikhailov <sergei.mikhailov@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\model\Lists\Business\Service;

use oat\oatbox\event\EventAggregator;
use oat\oatbox\session\SessionService;
use oat\oatbox\user\UserLanguageServiceInterface;
use oat\tao\model\Lists\Business\Contract\ValueCollectionRepositoryInterface;
use oat\tao\model\Lists\Business\Domain\ValueCollection;
use oat\tao\model\Lists\Business\Domain\ValueCollectionSearchRequest;
use oat\tao\model\Lists\Business\Event\ListSavedEvent;
use oat\tao\model\Lists\Business\Input\ValueCollectionDeleteInput;
use oat\tao\model\Lists\Business\Input\ValueCollectionSearchInput;
use oat\tao\model\Lists\DataAccess\Repository\ValueConflictException;
use oat\tao\model\service\InjectionAwareService;
use OverflowException;

class ValueCollectionService extends InjectionAwareService
{
    public const SERVICE_ID = 'tao/ValueCollectionService';

    /** @var ValueCollectionRepositoryInterface */
    private $repositories;

    /** @var int */
    private $maxItems = 0;

    public function __construct(ValueCollectionRepositoryInterface ...$repositories)
    {
        parent::__construct();

        $this->repositories = $repositories;
    }

    public function findAll(ValueCollectionSearchInput $input): ValueCollection
    {
        $searchRequest = $input->getSearchRequest();

        foreach ($this->repositories as $repository) {
            if (
                $searchRequest->hasValueCollectionUri()
                && !$repository->isApplicable($searchRequest->getValueCollectionUri())
            ) {
                continue;
            }
            $this->setUserDataLanguage($searchRequest);

            return $repository->findAll(
                $searchRequest
            );
        }

        return new ValueCollection();
    }

    public function delete(ValueCollectionDeleteInput $input): void
    {
        foreach ($this->repositories as $repository) {
            if ($repository->isApplicable($input->getValueCollectionUri())) {
                $repository->delete($input->getValueCollectionUri());
            }
        }
    }

    /**
     * @param ValueCollection $valueCollection
     *
     * @return bool
     *
     * @throws ValueConflictException
     * @throws OverflowException
     */
    public function persist(ValueCollection $valueCollection): bool
    {
        if ($this->maxItems > 0 && $valueCollection->count() > $this->maxItems) {
            throw new OverflowException("Collection exceeds the allowed number of items");
        }

        foreach ($this->repositories as $repository) {
            if ($repository->isApplicable($valueCollection->getUri())) {
                $this->getEventAggregator()->put(
                    $valueCollection->getUri(),
                    new ListSavedEvent($valueCollection->getUri())
                );

                return $repository->persist($valueCollection);
            }
        }

        return false;
    }

    public function count(ValueCollectionSearchInput $input): int
    {
        $searchRequest = $input->getSearchRequest();

        foreach ($this->repositories as $repository) {
            if (
                $searchRequest->hasValueCollectionUri()
                && !$repository->isApplicable($searchRequest->getValueCollectionUri())
            ) {
                continue;
            }

            return $repository->count(
                $searchRequest
            );
        }

        return 0;
    }

    public function setMaxItems(int $maxItems): void
    {
        $this->maxItems = $maxItems;
    }

    private function setUserDataLanguage(ValueCollectionSearchRequest $searchRequest): void
    {
        /** @var SessionService $userSession */
        $userSession = $this->getServiceLocator()->get(SessionService::class);
        $user = $userSession->getCurrentUser();

        /** @var UserLanguageServiceInterface $userLanguageService */
        $userLanguageService = $this->getServiceLocator()
            ->get(UserLanguageServiceInterface::SERVICE_ID);

        $searchRequest->setDataLanguage(
            $userLanguageService->getDataLanguage($user)
        );

        $searchRequest->setDefaultLanguage($userLanguageService->getDefaultLanguage());
    }

    private function getEventAggregator(): EventAggregator
    {
        return $this->getServiceManager()->getContainer()->get(EventAggregator::SERVICE_ID);
    }
}
