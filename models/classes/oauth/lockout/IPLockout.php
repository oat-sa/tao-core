<?php

declare(strict_types=1);

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
 * Copyright (c) 2020 (original work) (update and modification) Open Assessment Technologies SA (under the project TAO-PRODUCT)
 *
 */

namespace oat\tao\model\oauth\lockout;

use oat\oatbox\service\ConfigurableService;
use oat\oatbox\service\exception\InvalidService;
use oat\oatbox\service\exception\InvalidServiceManagerException;
use oat\tao\model\oauth\lockout\storage\LockoutStorageInterface;

/**
 * Lock based on IP
 * @package oat\tao\model\oauth\lockout
 */
class IPLockout extends ConfigurableService implements LockoutInterface
{
    /** Storage to store failed attempts  */
    public const OPTION_LOCKOUT_STORAGE = 'storage';
    /**  something that will return ip */
    public const OPTION_IP_FACTORY = 'IPFactory';
    /** Maximum failed attempt count */
    public const OPTION_THRESHOLD = 'threshold';
    /** Block time after last failed attempt when threshold reached */
    public const OPTION_TIMEOUT = 'timeout';
    /**
     * Flags to get client ip.
     * Default flags\order : HTTP_CLIENT_IP, HTTP_X_FORWARDED_FOR, HTTP_X_FORWARDED_FOR, REMOTE_ADDR
     */
    public const OPTION_SERVER_IP_FLAGS = 'SERVER_IP_FLAGS';

    /**
     * @throws InvalidService
     * @throws InvalidServiceManagerException
     */
    public function logFailedAttempt(): void
    {
        $this->getLockoutStorage()->store(
            $this->getOption(self::OPTION_IP_FACTORY)->create(),
            $this->getOption(self::OPTION_TIMEOUT)
        );
    }

    /**
     * @return bool
     * @throws InvalidService
     * @throws InvalidServiceManagerException
     */
    public function isAllowed(): bool
    {
        $failedAttempts = $this->getLockoutStorage()
            ->getFailedAttempts(
                $this->getOption(self::OPTION_IP_FACTORY)->create(),
                $this->getOption(self::OPTION_TIMEOUT)
            );
        return $failedAttempts < $this->getOption(self::OPTION_THRESHOLD);
    }

    /**
     * @return LockoutStorageInterface
     * @throws InvalidService
     * @throws InvalidServiceManagerException
     */
    protected function getLockoutStorage(): LockoutStorageInterface
    {
        return $this->getSubService(self::OPTION_LOCKOUT_STORAGE);
    }
}
