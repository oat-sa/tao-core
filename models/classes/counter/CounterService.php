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
 *
 */

declare(strict_types=1);

namespace oat\tao\model\counter;

use common_Exception;
use common_persistence_KeyValuePersistence;
use oat\oatbox\service\ConfigurableService;

/**
 *
 */
class CounterService extends ConfigurableService
{
    public const SERVICE_ID = 'tao/CounterService';
    public const OPTION_PERSISTENCE = 'persistence';
    public const OPTION_COUNTER_KEY_PREFIX = 'counterKeyPrefix';

    /**
     * @return common_persistence_KeyValuePersistence
     * @throws CounterServiceException
     */
    protected function getPersistence(): common_persistence_KeyValuePersistence
    {
        $persistenceId = $this->getOption(self::OPTION_PERSISTENCE);
        $persistence = common_persistence_KeyValuePersistence::getPersistence($persistenceId);

        if (!$persistence instanceof common_persistence_KeyValuePersistence) {
            $msg = "Persistence '${persistenceId}' must be an instance of '";
            $msg .= common_persistence_KeyValuePersistence::class . "', ";
            $msg .= get_class($persistence) . ' persistence given.';
            throw new CounterServiceException($msg);
        }

        return $persistence;
    }

    /**
     * @param string $counter
     * @param int $value
     * @throws CounterServiceException
     */
    public function increment(string $counter, int $value = 1): void
    {
        $this->getPersistence()->incr($this->buildPrefix($counter));
    }

    /**
     * @param string $counter
     * @param int $value
     * @throws CounterServiceException
     */
    public function decrement(string $counter, int $value = 1): void
    {
        $this->getPersistence()->decr($this->buildPrefix($counter));
    }

    /**
     * @param string $counter
     * @param int $value
     * @throws common_Exception|CounterServiceException
     */
    public function set(string $counter, int $value = 0): void
    {
        $this->getPersistence()->set($this->buildPrefix($counter), $value);
    }

    /**
     * @param string $counter
     * @return int
     * @throws CounterServiceException
     */
    public function get(string $counter): int
    {
        return (int)$this->getPersistence()->get($this->buildPrefix($counter));
    }

    /**
     * @param string $counter
     * @return string
     */
    protected function buildPrefix(string $counter): string
    {
        return $this->getOption(self::OPTION_COUNTER_KEY_PREFIX) . $counter;
    }
}
