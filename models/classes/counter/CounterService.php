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

declare(strict_types = 1);

namespace oat\tao\model\counter;

use common_Exception;
use common_persistence_KeyValuePersistence as KeyValuePersistence;
use oat\oatbox\event\Event;
use oat\oatbox\event\EventManager;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\service\exception\InvalidServiceManagerException;
use oat\oatbox\service\ServiceNotFoundException;

class CounterService extends ConfigurableService
{
    public const SERVICE_ID = 'tao/CounterService';

    public const OPTION_PERSISTENCE = 'persistence';
    public const OPTION_COUNTER_KEY_PREFIX = 'counterKeyPrefix';
    public const OPTION_EVENTS = 'events';

    public const DEFAULT_PREFIX = 'tao:counter:';

    protected const OPTION_CALLBACK = 'keyCallbackMethod';
    protected const OPTION_SHORT_NAME = 'shortName';

    /**
     * @return KeyValuePersistence
     * @throws CounterServiceException
     */
    protected function getPersistence(): KeyValuePersistence
    {
        $persistenceId = $this->getOption(self::OPTION_PERSISTENCE);
        $persistence = KeyValuePersistence::getPersistence($persistenceId);

        if (!$persistence instanceof KeyValuePersistence) {
            $msg = "Persistence '${persistenceId}' must be an instance of '";
            $msg .= KeyValuePersistence::class . "', ";
            $msg .= get_class($persistence) . ' persistence given.';
            throw new CounterServiceException($msg, CounterServiceException::CODE_INVALID_PERSISTENCE);
        }

        return $persistence;
    }

    /**
     * @param string      $eventFqcn
     * @param string      $shortName
     * @param string|null $keyCallbackMethod
     *
     * @throws CounterServiceException
     * @throws InvalidServiceManagerException
     * @throws ServiceNotFoundException
     * @throws common_Exception
     */
    public function attach(string $eventFqcn, string $shortName, ?string $keyCallbackMethod = null): void
    {
        if (!class_exists($eventFqcn)) {
            $msg = "No event class found with FQCN '${$eventFqcn}' in available code base.";
            throw new CounterServiceException($msg, CounterServiceException::CODE_UNKNOWN_EVENT_TYPE);
        }

        /** @var EventManager $eventManager */
        $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
        $eventManager->attach($eventFqcn, [self::SERVICE_ID, 'increment']);
        $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);

        $eventOptions = $this->getOption(self::OPTION_EVENTS);
        $eventOptions[$eventFqcn] = [
            self::OPTION_CALLBACK => $keyCallbackMethod,
            self::OPTION_SHORT_NAME => $shortName
        ];
        $this->setOption(self::OPTION_EVENTS, $eventOptions);

        $this->logDebug("CounterService now listening to events with FQCN '${eventFqcn}'");
    }

    /**
     * @param string $eventFqcn
     *
     * @throws InvalidServiceManagerException|CounterServiceException
     * @throws \oat\oatbox\service\ServiceNotFoundException
     * @throws \common_Exception
     */
    public function detach(string $eventFqcn): void
    {
        $eventOptions = $this->getOption(self::OPTION_EVENTS);

        if (!array_key_exists($eventFqcn, $eventOptions)) {
            $msg = "No event with FQCN '${eventFqcn}' already attached for counting.";
            throw new CounterServiceException($msg, CounterServiceException::CODE_UNKNOWN_EVENT_TYPE);
        }

        /** @var EventManager $eventManager */
        $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
        $eventManager->detach($eventFqcn, [self::SERVICE_ID, 'increment']);
        $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);

        unset($eventOptions[$eventFqcn]);

        $this->setOption(self::OPTION_EVENTS, $eventOptions);

        $this->logDebug("CounterService not listening anymore to events with FQCN '${eventFqcn}'.");
    }

    /**
     * @param Event $event
     * @throws CounterServiceException
     */
    public function increment(Event $event): void
    {
        $eventOptions = $this->getOption(self::OPTION_EVENTS);
        $eventFqcn = get_class($event);

        if (!array_key_exists($eventFqcn, $eventOptions)) {
            throw new CounterServiceException(
                "Configuration Violation. No event with FQCN '${eventFqcn}' registered while incrementing."
            );
        }

        $eventOption = $eventOptions[$eventFqcn];
        $keyCallbackMethod = $eventOption[self::OPTION_CALLBACK];
        $keyCallbackValue = null;

        if (!empty($keyCallbackMethod)) {
            $keyCallbackValue = $event->$keyCallbackMethod();
        }

        $this->getPersistence()->incr($this->buildKey($eventFqcn, $keyCallbackValue));
    }

    /**
     * @param string $eventFqcn
     * @param int|null $value
     * @param string|null $suffix
     * @throws CounterServiceException
     * @throws common_Exception
     */
    public function reset(string $eventFqcn, ?int $value = 0, ?string $suffix = null): void
    {
        $this->getPersistence()->set($this->buildKey($eventFqcn, $suffix), $value);
    }

    /**
     * @param string $eventFqcn
     * @param string|null $keyCallbackValue
     * @return int
     * @throws CounterServiceException
     */
    public function get(string $eventFqcn, ?string $keyCallbackValue = null): int
    {
        return (int)$this->getPersistence()->get($this->buildKey($eventFqcn, $keyCallbackValue));
    }

    /**
     * @param string $eventFqcn
     * @param string|null $keySuffix
     * @return string
     */
    protected function buildKey(string $eventFqcn, ?string $keySuffix = null): string
    {
        $counterIdentifier = $eventFqcn;
        $eventOption = $this->getOption(self::OPTION_EVENTS);

        if (!empty($eventOption[$eventFqcn][self::OPTION_SHORT_NAME])) {
            $counterIdentifier = $eventOption[$eventFqcn][self::OPTION_SHORT_NAME];
        }

        $key = $this->getOption(self::OPTION_COUNTER_KEY_PREFIX) . $counterIdentifier;

        if (!empty($keySuffix)) {
            $key .= '_' . $keySuffix;
        }

        return $key;
    }
}
