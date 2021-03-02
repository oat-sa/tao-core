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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\scripts\tools;

use Exception;
use oat\oatbox\event\EventManager;
use oat\oatbox\event\EventManagerAwareTrait;
use oat\oatbox\extension\script\ScriptAction;
use oat\oatbox\reporting\Report;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\service\ServiceManagerAwareTrait;
use oat\tao\model\webhooks\configEntity\Webhook;
use oat\tao\model\webhooks\configEntity\WebhookAuth;
use oat\tao\model\webhooks\WebhookEventsService;
use oat\tao\model\webhooks\WebhookRegistryManager;
use oat\tao\model\webhooks\WebhookRegistryManagerInterface;
use oat\tao\model\webhooks\WebhookSerializableEventInterface;
use oat\taoPublishing\model\publishing\event\RemoteDeliveryCreatedEvent;
use ReflectionClass;

class RegisterEventWebhook extends ScriptAction
{
    use ServiceManagerAwareTrait;
    use EventManagerAwareTrait;

    private const MAX_RETRY = 5;
    private const DEFAULT_HTTP_METHOD = 'GET';

    private const ACCEPTED_HTTP_METHOD = [
        'POST',
        self::DEFAULT_HTTP_METHOD
    ];

    /** @var Report */
    private $report;

    protected function provideOptions()
    {
        return [
            'simpleRosterUrl' => [
                'prefix' => 'u',
                'longPrefix' => 'url',
                'flag' => false,
                'description' => 'Will register endpoint to inform about new deliveries published',
                'required' => true
            ],
            'httpMethod' => [
                'prefix' => 'm',
                'description' => 'Determine what type of http method use',
                'flag' => false,
                'required' => false
            ],
            'event' => [
                'prefix' => 'e',
                'longPrefix' => 'event',
                'flag' => false,
                'description' => 'Target event FQN that has to be registered as webhook',
                'required' => true
            ],
            'auth' => [
                'prefix' => 'a',
                'longPrefix' => 'auth',
                'flag' => false,
                'required' => false,
                'description' => 'Provide authentication class that you want to use'
            ]
        ];
    }

    protected function provideDescription()
    {
        return 'Script will register webhook with a defined url';
    }

    protected function run()
    {
        $this->report = Report::createInfo('Registering webhook');

        /** @var ConfigurableService $webhookEventsService */
        $webhookEventsService = $this->getServiceLocator()->get(WebhookEventsService::class);

        try {
            $this->validateEvent();
            $webhookEventsService->registerEvent(RemoteDeliveryCreatedEvent::class);
            $this->getServiceManager()->register(WebhookEventsService::SERVICE_ID, $webhookEventsService);
            $this->getServiceManager()->register(EventManager::SERVICE_ID, $this->getEventManager());
        } catch (Exception $exception) {
            $this->report->add(
                Report::createError('Registering failed')
            );
        }

        $this->getWebhookRegistryManager()->addWebhookConfig(
            $this->createWebHook(),
            RemoteDeliveryCreatedEvent::class
        );

        $this->report->add(
            Report::createInfo('Webhook created')
        );

        return $this->report;
    }

    private function getWebhookRegistryManager(): WebhookRegistryManagerInterface
    {
        return $this->getServiceLocator()->get(WebhookRegistryManager::class);
    }

    private function getHttpMethod(): string
    {
        $method = $this->getOption('httpMethod') ?? self::DEFAULT_HTTP_METHOD;
        if (!in_array($method, self::ACCEPTED_HTTP_METHOD, true)) {
            $this->report->add(
                Report::createWarning(sprintf('Used illegal method %s. Falling back to default method', $method))
            );
            return self::DEFAULT_HTTP_METHOD;
        }

        return $method;
    }

    private function getWebhookAuth(): ?WebhookAuth
    {
        if ($authenticationClass = $this->getOption('auth')) {
            if ($this->isAuthenticationClassIsValid($authenticationClass)) {
                return new WebhookAuth(
                    $authenticationClass,
                    []
                );
            }
        }

        return null;
    }

    private function createWebHook(): Webhook
    {
        return new Webhook(
            'remoteDeliveryWebHook',
            $this->getOption('simpleRosterUrl'),
            $this->getHttpMethod(),
            self::MAX_RETRY,
            $this->getWebhookAuth(),
            false
        );
    }

    private function validateEvent(): void
    {
        $eventName = $this->getOption('event');

        if (!$this->eventImplementsWebhook($eventName)) {
            $this->report->add(
                Report::createError(sprintf('Provided event: %s does not exist or is not implementing WebhookInterface', $eventName))
            );
        }
    }

    private function eventImplementsWebhook(string $event): bool
    {
        if (class_exists($event)) {
            $event = new ReflectionClass($event);
            return in_array(WebhookSerializableEventInterface::class, $event->getInterfaceNames(), true);
        }

        return false;
    }

    private function isAuthenticationClassIsValid(string $authenticationClass): bool
    {
        return class_exists($authenticationClass);
    }
}
