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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\model\lock\implementation;

use common_exception_InconsistentData;
use core_kernel_classes_Resource;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\lock\LockSystem;

/**
 * Implements a non locking Lock
 *
 * @note It would be preferably static but we may want to have the polymorphism on lock but it would be prevented by explicit class method static calls.
 * Also if you nevertheless call it statically you may want to avoid the late static binding for the getLockProperty
 */
class NoLock extends ConfigurableService implements LockSystem
{
    /**
     * Do nothing
     * @param core_kernel_classes_Resource $resource
     * @param core_kernel_classes_Resource $ownerId
     */
    public function setLock(core_kernel_classes_Resource $resource, $ownerId): void
    {
        // do nothing
    }

    /**
     * always returns false
     * @return boolean
     */
    public function isLocked(core_kernel_classes_Resource $resource)
    {
        return false;
    }

    /**
     * does nothing
     *
     * @param core_kernel_classes_Resource $resource
     * @param core_kernel_classes_Resource $ownerId
     */
    public function releaseLock(core_kernel_classes_Resource $resource, $ownerId): void
    {
    }

    /**
     * does nothing
     *
     * @param core_kernel_classes_Resource $resource
     */
    public function forceReleaseLock(core_kernel_classes_Resource $resource): void
    {
    }

    /**
     * Throws an exception as no lock can exist
     *
     * @throws common_exception_InconsistentData
     */
    public function getLockData(core_kernel_classes_Resource $resource)
    {
        return null;
    }
}
