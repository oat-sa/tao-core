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

namespace oat\tao\model\webhooks;

use common_Exception;
use core_kernel_classes_Resource;
use core_kernel_persistence_Exception;
use Laminas\ServiceManager\ServiceLocatorAwareInterface;
use Laminas\ServiceManager\ServiceLocatorAwareTrait;
use oat\tao\model\webhooks\configEntity\WebhookInterface;

class WebhookRdfRegistry implements WebhookRegistryInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * @throws core_kernel_persistence_Exception
     * @throws common_Exception
     */
    public function getWebhookConfig($id): ?WebhookInterface
    {
        return $this->getClassService()->getWebhookByUri($id);
    }

    public function getWebhookConfigIds($eventName): array
    {
        $webHooks = $this->getClassService()->getWebhookByEventClass($eventName);

        return array_map(static function (core_kernel_classes_Resource $webHook) {
            return $webHook->getUri();
        }, $webHooks);
    }

    private function getClassService(): WebHookClassService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(WebHookClassService::class);
    }
}
