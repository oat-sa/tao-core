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

namespace oat\tao\scripts\update;

use oat\oatbox\extension\AbstractAction;
use oat\oatbox\service\ServiceNotFoundException;
use oat\tao\model\webhooks\WebhookFileRegistry;
use oat\tao\model\webhooks\WebhookRdfRegistry;

class MigrateWebhooksConfigsFromFileToRdf extends AbstractAction
{
    public function __invoke($params)
    {
        $serviceManager = $this->getServiceManager();

        try {
            /** @var WebhookFileRegistry $fileRegistry */
            $fileRegistry = $serviceManager->get(WebhookFileRegistry::SERVICE_ID);


        } catch (ServiceNotFoundException $e) {
            $this->logError('WebhookFileRegistry not found');

            return;
        }

        if (!($fileRegistry instanceof WebhookFileRegistry)) {
            $this->logError(sprintf('%s expected, %s found', WebhookFileRegistry::class, get_class($fileRegistry)));

            return;
        }

        $rdfRegistry = $serviceManager->getContainer()->get(WebhookRdfRegistry::class);


        $webHooks = $fileRegistry->getWebhooks();
        $preparedWebHooks = [];
        foreach ($webHooks as $webHook) {
            $preparedWebHooks[$webHook->getId()] = $webHook;
        }

        $events = $fileRegistry->getOption(WebhookFileRegistry::OPTION_EVENTS);

        foreach ($events as $eventClass => $webhookIds) {
            foreach ($webhookIds as $webhookId) {
                $rdfRegistry->addWebhook($preparedWebHooks[$webhookId], [$eventClass]);
            }
        }
    }
}
