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
 *
 *
 */

namespace oat\tao\model\mutex;

use Symfony\Component\Lock\Lock;

/**
 * Trait LockTrait
 *
 * @package oat\tao\model\mutex
 */
trait LockTrait
{
    /**
     * @see \Symfony\Component\Lock\Factory::createLock()
     * @param $resource
     * @param float $ttl
     * @param bool $autoRelease
     * @return Lock
     */
    public function createLock($resource, $ttl = 300.0, $autoRelease = true)
    {
        return $this->getServiceLocator()
            ->get(LockService::SERVICE_ID)
            ->getLockFactory()
            ->createLock($resource, $ttl, $autoRelease);
    }
}