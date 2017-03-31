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

    protected $driver;

    public function __construct(\common_persistence_KeyValuePersistence $driver)
    {
        $this->driver = $driver;
    }

    public function setPlatformState(MaintenanceState $state)
    {
        $currentTimestamp = $this->getCurrentTimestamp();

        if ($previous = $this->getDriver()->get(self::PREFIX . self::LAST_MODE)) {
            $currentState = new MaintenanceState(json_decode($previous, true));
            $currentState->setEndTime($currentTimestamp);
            $this->getDriver()->set(self::PREFIX . $currentState->getId(), json_encode($currentState->toArray()));

            $state->setId($currentState->getId() + 1);
        }


        $this->getDriver()->set(self::PREFIX . self::LAST_MODE, json_encode($state->toArray()));
    }

    public function getHistory()
    {
        $i = 1;
        $history = array(
            $i => new MaintenanceState(json_decode($this->getDriver()->get(self::PREFIX . self::LAST_MODE), true))
        );
        while ($data = json_decode($this->getDriver()->get(self::PREFIX . $i), true)) {
            $history[$i] = new MaintenanceState($data);
            $i++;
        }
        return $history;
    }

    public function getCurrentPlatformState()
    {
        $data = json_decode($this->getDriver()->get(self::PREFIX . self::LAST_MODE), true);
        if (! $data) {
            throw new \common_exception_NotFound();
        }
        return new MaintenanceState($data);
    }

    protected function getCurrentTimestamp()
    {
        return (new \DateTime())->format('Ymdhis');
    }

    protected function getDriver()
    {
        if (! $this->driver) {
            throw new \common_Exception(__('Maintenance storage driver is not set'));
        }
        return $this->driver;
    }

}