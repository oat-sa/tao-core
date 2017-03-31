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
 * Copyright (c) 2017 Open Assessment Technologies SA
 *
 */

namespace oat\tao\model\maintenance;

class MaintenanceStorage
{
    const LAST_MODE = 'last';

    const PREFIX = 'maintenance_';

    /**
     * Driver to access KeyValue storage
     *
     * @var \common_persistence_KeyValuePersistence
     */
    protected $driver;

    /**
     * MaintenanceStorage constructor.
     *
     * @param \common_persistence_KeyValuePersistence $driver
     */
    public function __construct(\common_persistence_KeyValuePersistence $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Persist the maintenance state
     *
     * If old maintenance exists, set key with old state id
     * Persist new maintenance state with key 'last'
     *
     * @param MaintenanceState $state
     */
    public function setPlatformState(MaintenanceState $state)
    {
        if ($previous = $this->getDriver()->get(self::PREFIX . self::LAST_MODE)) {
            $currentState = new MaintenanceState(json_decode($previous, true));
            $currentState->setEndTime($state->getStartTime());
            $this->getDriver()->set(self::PREFIX . $currentState->getId(), json_encode($currentState->toArray()));

            $state->setId($currentState->getId() + 1);
        }


        $this->getDriver()->set(self::PREFIX . self::LAST_MODE, json_encode($state->toArray()));
    }

    /**
     * Get maintenance history as list of state
     *
     * @todo Return an arrayIterator
     * @return array
     */
    public function getHistory()
    {
        $history = array(
            1 => new MaintenanceState(json_decode($this->getDriver()->get(self::PREFIX . self::LAST_MODE), true))
        );

        $i = 2;
        while ($data = json_decode($this->getDriver()->get(self::PREFIX . $i), true)) {
            $history[$i] = new MaintenanceState($data);
            $i++;
        }
        return $history;
    }

    /**
     * Get the current state of the platform
     *
     * @return MaintenanceState
     * @throws \common_exception_NotFound If no state is set
     */
    public function getCurrentPlatformState()
    {
        $data = json_decode($this->getDriver()->get(self::PREFIX . self::LAST_MODE), true);
        if (! $data) {
            throw new \common_exception_NotFound();
        }
        return new MaintenanceState($data);
    }

    /**
     * Get the driver to access KeyValue persistence
     *
     * @return \common_persistence_KeyValuePersistence
     * @throws \common_Exception
     */
    protected function getDriver()
    {
        if (! $this->driver) {
            throw new \common_Exception(__('Maintenance storage driver is not set'));
        }
        return $this->driver;
    }

}