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

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;
use oat\tao\model\event\ClassPropertyRemovedEvent;
use oat\tao\model\search\tasks\DeleteIndexProperty;
use oat\tao\model\taskQueue\QueueDispatcherInterface;

class ClassPropertyRemovedListener extends ConfigurableService
{
    const SERVICE_ID = 'tao/ClassPropertyRemovedListener';

    public function handleEvent(ClassPropertyRemovedEvent $event): void
    {
        if (!$this->getServiceLocator()->get(AdvancedSearchChecker::class)->isEnabled()) {
            return;
        }

        $taskMessage = __('Updating search index');

        /** @var QueueDispatcherInterface $queueDispatcher */
        $queueDispatcher = $this->getServiceLocator()->get(QueueDispatcherInterface::SERVICE_ID);
        $queueDispatcher->createTask(
            new DeleteIndexProperty(),
            [
                $event->getClass(),
                $event->getPropertyName()
            ],
            $taskMessage
        );
    }
}
