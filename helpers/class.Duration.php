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
 * Copyright (c) 2013 -  (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

use oat\tao\helpers\DateIntervalMS;

/**
 * Helps you to manipulate durations.
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @package tao
 */
class tao_helpers_Duration
{
    /**
     * Converts a time string to an ISO8601 duration
     * @param string $time as hh:mm:ss.micros
     * @return string the ISO duration
     */
    public static function timetoDuration($time)
    {
        $duration = 'PT';

        $regexp = "/^([0-9]{2}):([0-9]{2}):([0-9]{2})(\.[0-9]{1,6})?$/";

        if (preg_match($regexp, $time ?? '', $matches)) {
            $duration .= (int)$matches[1] . 'H' . (int)$matches[2] . 'M';
            $duration .= isset($matches[4])
                ? (int)$matches[3] . $matches[4] . 'S'
                : (int)$matches[3] . 'S';
        } else {
            $duration .= '0S';
        }

        return $duration;
    }

    /**
     * Converts  an interval to a time
     * @param DateInterval $interval
     * @return string time hh:mm:ss
     */
    public static function intervalToTime(DateInterval $interval)
    {
        $time = null;

        if (!is_null($interval)) {
            $format = property_exists(get_class($interval), 'u') ? '%H:%I:%S.%U' : '%H:%I:%S';
            $time = $interval->format($format);
        }

        return $time;
    }

    /**
     * Converts a duration to a time
     * @param string $duration the ISO duration
     * @return string time hh:mm:ss.micros
     */
    public static function durationToTime($duration)
    {
        $time = null;

        try {
            $duration ??= '';
            $interval = preg_match('/(\.[0-9]{1,6}S)$/', $duration)
                ? new DateIntervalMS($duration)
                : new DateInterval($duration);
            $time = self::intervalToTime($interval);
        } catch (Exception $e) {
            common_Logger::e($e->getMessage());
        }

        return $time;
    }
}
