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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\tao\model\settings;

/**
 * Persistence for settings
 */
interface SettingsStorageInterface
{
    const SERVICE_ID = 'tao/settingsStorage';

    /**
     * Store the given setting
     *
     * @param string $settingId
     * @param mixed $data
     * @return boolean
     */
    public function set($settingId, $data);

    /**
     * Retrieve the given setting's value or null if no setting is found
     *
     * @param string $settingId
     * @return mixed
     */
    public function get($settingId);

    /**
     * Verifies if a given setting already exists
     *
     * @param string $settingId
     * @return boolean
     */
    public function exists($settingId);

    /**
     * Remove the given setting from storage
     *
     * @param string $settingId
     * @return boolean
     */
    public function del($settingId);
}
