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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\state;

/**
 * Persistence for the item delivery service
 */
interface StateStorage
{
    const SERVICE_ID = 'tao/stateStorage';

    /**
     * Store the state of the service call
     *
     * @param string $userId
     * @param string $callId
     * @param string $data
     * @return boolean
     */
    public function set($userId, $callId, $data);

    /**
     * Retore the state of the service call
     * Returns null if no state is found
     *
     * @param string $userId
     * @param string $callId
     * @return string
     */
    public function get($userId, $callId);

    /**
     * Whenever or not a state for this service call exists
     *
     * @param string $userId
     * @param string $callId
     * @return boolean
     */
    public function has($userId, $callId);

    /**
     * Remove the state for this service call
     *
     * @param string $userId
     * @param string $callId
     * @return boolean
     */
    public function del($userId, $callId);
}