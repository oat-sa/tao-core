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

    const DATEDIFF_FORMAT = '%y years, %m months, %d days %H:%I:%S';

    protected static $availableStatus = array(self::LIVE_MODE, self::OFFLINE_MODE);

    /**
     * The id to identify a Maintenance state
     *
     * @var integer
     */
    protected $id;

    /**
     * The datetime when MaintenanceState was begun
     *
     * @var \DateTime
     */
    protected $startTime;

    /**
     * The datetime when MaintenanceState was ended
     *
     * @var \DateTime
     */
    protected $endTime = null;

    /**
     * The maintenance status, must be be in self::$availableStatus
     *
     * @var mixed
     */
    protected $status;


    /**
     * MaintenanceState constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->checkData($data);
        $this->id     = isset($data[self::ID]) ? $data[self::ID] : 1;
        $this->status = $data[self::STATUS];

        if (isset($data[self::START_TIME])) {
            $this->startTime = $this->getDateTime($data[self::START_TIME]);
        } else {
            $this->startTime = new \DateTime();
        }

        if (isset($data[self::END_TIME])) {
            $this->endTime = $this->getDateTime($data[self::END_TIME]);
        }
    }

    /**
     * Return the Maintenance state as array, Datetime are converted to timestamp
     *
     * @return array
     */
    public function toArray()
    {
        $data = array(
            self::ID         => $this->id,
            self::STATUS     => $this->status,
            self::START_TIME => $this->startTime->getTimestamp(),
        );

        if (! is_null($this->endTime)) {
            $data[self::END_TIME] = $this->endTime->getTimestamp();
        }

        return $data;
    }

    /**
     * @return int|mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param $endTime
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $this->getDateTime($endTime);
    }

    /**
     * @return \DateTime
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @return bool
     */
    public function getBooleanStatus()
    {
        return $this->status === self::LIVE_MODE ? true : false;
    }

    /**
     * @return \DateInterval
     */
    public function getDuration()
    {
        $endTime = $this->endTime ?: new \DateTime();
        return $this->startTime->diff($endTime);
    }

    /**
     * Transform a string|Datetime to Datetime
     *
     * @param $dateTime
     * @return \DateTime
     * @throws \common_Exception
     */
    protected function getDateTime($dateTime)
    {
        if ($dateTime instanceof \DateTime) {
            return $dateTime;
        }

        if ((is_string($dateTime) && (int) $dateTime > 0) || is_numeric($dateTime)) {
            return (new \DateTime())->setTimestamp($dateTime);

        }

        throw new \common_Exception(__('A date has to be a Datetime or timestamp'));
    }

    /**
     * Check data of constructor input
     *
     * @param array $data
     * @throws \common_Exception
     */
    protected function checkData(array $data)
    {
        if (! isset($data[self::STATUS]) || ! in_array($data[self::STATUS], self::$availableStatus)) {
            throw new \common_Exception(
                __('A maintenance status must have a STATUS: "%s" or "%s"', self::LIVE_MODE, self::OFFLINE_MODE)
            );
        }
    }
}