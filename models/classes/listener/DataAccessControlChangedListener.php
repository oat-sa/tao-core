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
 */

declare(strict_types=1);

namespace oat\tao\model\listener;

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;
use oat\tao\model\event\DataAccessControlChangedEvent;
use oat\tao\model\search\tasks\UpdateDataAccessControlInIndex;
use oat\tao\model\taskQueue\QueueDispatcherInterface;

class DataAccessControlChangedListener extends ConfigurableService
{
    use OntologyAwareTrait;

    public const SERVICE_ID = 'tao/DataAccessControlChangedListener';

    public function handleEvent(DataAccessControlChangedEvent $event): void
    {
        $this->getLogger()->debug('triggering index update on DataAccessControlChanged event');

        if (!$this->getServiceLocator()->get(AdvancedSearchChecker::class)->isEnabled()) {
            return;
        }

        $taskMessage = __('Adding/updating search index for updated resource');

        /** @noinspection PhpUnhandledExceptionInspection */
        $resource = $this->getResource($event->getResourceId());

        if ($resource->isClass() && !$event->isRecursive()) {
            return;
        }

        $queueDispatcher = $this->getServiceLocator()->get(QueueDispatcherInterface::SERVICE_ID);
        $queueDispatcher->createTask(
            new UpdateDataAccessControlInIndex(),
            [
                $resource->getUri(),
                $event->getOperations('add'),
            ],
            $taskMessage
        );
    }
}
