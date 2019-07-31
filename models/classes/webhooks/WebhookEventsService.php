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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\model\webhooks;

use oat\oatbox\event\Event;
use oat\oatbox\event\EventManager;
use oat\oatbox\log\LoggerAwareTrait;
use oat\oatbox\service\ConfigurableService;

class WebhookEventsService extends ConfigurableService implements WebhookEventsServiceInterface
{
    use LoggerAwareTrait;

    /**
     * Option value is array of ['eventName' => true, ... ] of supported events
     * Such array structure is needed to perform quick search by key
     */
    const OPTION_SUPPORTED_EVENTS = 'supportedEvents';

    /**
     * @inheritDoc
     */
    public function registerEvent($eventName, EventManager $eventManager)
    {
        $supportedEvents = $this->getRegisteredEvents();
        $supportedEvents[$eventName] = true;
        $this->setOption(self::OPTION_SUPPORTED_EVENTS, $supportedEvents);

        $eventManager->attach($eventName, $this->getEventHandlerCallback());
    }

    /**
     * @inheritDoc
     */
    public function unregisterEvent($eventName, EventManager $eventManager)
    {
        $supportedEvents = $this->getRegisteredEvents();
        unset($supportedEvents[$eventName]);
        $this->setOption(self::OPTION_SUPPORTED_EVENTS, $supportedEvents);

        $eventManager->detach($eventName, $this->getEventHandlerCallback());
    }

    /**
     * @inheritDoc
     */
    public function isEventRegistered($eventName)
    {
        $supportedEvents = $this->getRegisteredEvents();
        return isset($supportedEvents[$eventName]);
    }

    /**
     * @return string[]
     */
    public function getRegisteredEvents()
    {
        $events = $this->getOption(self::OPTION_SUPPORTED_EVENTS);
        return $events !== null
            ? $events
            : [];
    }

    public function handleEvent(Event $event)
    {
        if (!$this->checkEventIsSupported($event)) {
            return;
        }

        /** @var WebhookSerializableEventInterface $event */

        $webhookConfigIds = $this->getWebhookRegistry()->getWebhookConfigIds($event->getName());
        if (count($webhookConfigIds) === 0) {
            return;
        }

        $this->createTasksForEvent($event, $webhookConfigIds);
    }

    /**
     * @param Event $event
     * @return bool
     */
    private function checkEventIsSupported(Event $event)
    {
        $eventName = $event->getName();

        if (!$this->isEventRegistered($eventName)) {
            $this->logError("Event '$eventName' is not supported by " . self::class);
            return false;
        }

        if (!($event instanceof WebhookSerializableEventInterface)) {
            $this->logError(sprintf('Event "%s" passed to "%s" is not implementing "%s"',
                $eventName,
                self::class,
                WebhookSerializableEventInterface::class
            ));
            return false;
        }

        return true;
    }

    /**
     * @param WebhookSerializableEventInterface $event
     * @param string[] $webhookConfigIds
     */
    private function createTasksForEvent(WebhookSerializableEventInterface $event, $webhookConfigIds) {
        $eventName = $event->getName();

        try {
            $eventData = $event->serializeForWebhook();
        }
        catch (\Exception $exception) {
            $this->logError("Error during '$eventName' event serialization for webhook. " . $exception->getMessage());
            return;
        }

        foreach ($webhookConfigIds as $webhookConfigId) {
            $webhookTaskMetadata = new WebhookTaskMetadata(
                $eventName,
                $eventData,
                $webhookConfigId
            );

            try {
                $this->getWebhookTaskService()->createTask($webhookTaskMetadata);
            }
            catch (\Exception $exception) {
                $this->logError(
                    "Can't create webhook task for '$eventName'" . $exception->getMessage(),
                    $webhookTaskMetadata
                );
                continue;
            }
        }
    }

    /**
     * @return array|callable
     */
    private function getEventHandlerCallback()
    {
        return [self::SERVICE_ID, 'handleEvent'];
    }

    /**
     * @return WebhookRegistryInterface
     */
    private function getWebhookRegistry()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(WebhookRegistryInterface::SERVICE_ID);
    }

    /**
     * @return WebhookTaskServiceInterface
     */
    private function getWebhookTaskService()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(WebhookTaskServiceInterface::SERVICE_ID);
    }
}
