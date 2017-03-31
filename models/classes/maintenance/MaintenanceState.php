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

class MaintenanceState
{
    const ID         = 'id';
    const STATUS     = 'status';
    const START_TIME = 'start';
    const END_TIME   = 'end';

    const LIVE_MODE = 'on';
    const OFFLINE_MODE = 'off';

    const DATETIME_FORMAT = 'Ymdhis';

    protected static $availableStatus = array(self::LIVE_MODE, self::OFFLINE_MODE);

    protected $id;

    protected $startTime;

    protected $endTime = null;

    protected $status;

    public function __construct(array $data)
    {
        $this->checkData($data);
        $this->id        = isset($data[self::ID]) ? $data[self::ID] : 1;
        $this->status    = $data[self::STATUS];
        $this->setStartTime(isset($data[self::START_TIME]) ? $data[self::START_TIME] : new \DateTime());

        if (isset($data[self::END_TIME])) {
            $this->setEndTime($data[self::END_TIME]);
        }
    }

    public function toArray()
    {
        $data = array(
            self::ID         => $this->id,
            self::STATUS     => $this->status,
            self::START_TIME => $this->getStringStartTime(),
        );

        if (! is_null($this->endTime)) {
            $data[self::END_TIME] = $this->getStringEndTime();
        }

        return $data;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setEndTime($endTime)
    {
        $this->endTime = $this->getDateTime($endTime);
    }

    public function getBooleanStatus()
    {
        return $this->status === self::LIVE_MODE ? true : false;
    }

    protected function setStartTime($startTime)
    {
        $this->startTime = $this->getDateTime($startTime);
    }

    protected function getStringEndTime()
    {
        return $this->endTime->format(self::DATETIME_FORMAT);
    }

    protected function getStringStartTime()
    {
        return $this->startTime->format(self::DATETIME_FORMAT);
    }

    protected function getDateTime($dateTime)
    {
        if ($dateTime instanceof \DateTime) {
            return $dateTime;
        }

        if (
            ((is_string($dateTime) && (int) $dateTime > 0) || is_numeric($dateTime))
            && strlen($dateTime) == 14
        ) {
            return \DateTime::createFromFormat(self::DATETIME_FORMAT, $dateTime);
        }

        throw new \common_Exception(__('A date has to be a Datetime or string in format %s', self::DATETIME_FORMAT));
    }

    protected function checkData(array $data)
    {
        if (! isset($data[self::STATUS]) || ! in_array($data[self::STATUS], self::$availableStatus)) {
            var_dump($data[self::STATUS]);
            throw new \common_Exception(
                __('A maintenance status must have a STATUS: "%s" or "%s"', self::LIVE_MODE, self::OFFLINE_MODE)
            );
        }
    }
}