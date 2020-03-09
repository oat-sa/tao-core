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
 * Copyright (c) 2015-2019 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\helpers\dateFormatter;

use common_Logger as Logger;
use common_session_SessionManager as SessionManager;
use DateTime;
use DateTimeZone;
use oat\oatbox\Configurable;
use tao_helpers_Date as DateHelper;

/**
 * Utility to display dates.
 */
class AbstractDateFormatter extends Configurable implements DateFormatterInterface
{
    const REPLACEMENTS = [
        'A' => 'A',      // for the sake of escaping below
        'a' => 'a',      // for the sake of escaping below
        'B' => '',       // Swatch internet time (.beats), no equivalent
        'c' => 'YYYY-MM-DD[T]HH:mm:ssZ', // ISO 8601
        'D' => 'ddd',
        'd' => 'DD',
        'e' => 'zz',     // deprecated since version 1.6.0 of moment.js
        'F' => 'MMMM',
        'G' => 'H',
        'g' => 'h',
        'H' => 'HH',
        'h' => 'hh',
        'I' => '',       // Daylight Saving Time? => moment().isDST();
        'i' => 'mm',
        'j' => 'D',
        'L' => '',       // Leap year? => moment().isLeapYear();
        'l' => 'dddd',
        'M' => 'MMM',
        'm' => 'MM',
        'N' => 'E',
        'n' => 'M',
        'O' => 'ZZ',
        'o' => 'YYYY',
        'P' => 'Z',
        'r' => 'ddd, DD MMM YYYY HH:mm:ss ZZ', // RFC 2822
        'S' => 'o',
        's' => 'ss',
        'T' => 'z',      // deprecated since version 1.6.0 of moment.js
        't' => '',       // days in the month => moment().daysInMonth();
        'U' => 'X',
        'u' => 'SSSSSS', // microseconds
        'v' => 'SSS',    // milliseconds (from PHP 7.0)
        'W' => 'W',      // for the sake of escaping below
        'w' => 'e',
        'Y' => 'YYYY',
        'y' => 'YY',
        'Z' => '',       // time zone offset in minutes => moment().zone();
        'z' => 'DDD',
    ];

    protected $datetimeFormats = [];

    /**
     * {@inheritdoc}
     */
    public function format($timestamp, $format, DateTimeZone $timeZone = null)
    {
        // Creates DateTime with microseconds.
        $dateTime = DateTime::createFromFormat('U.u', sprintf('%.f', $timestamp));
        if ($timeZone === null) {
            $timeZone = new DateTimeZone(SessionManager::getSession()->getTimeZone());
        }
        $dateTime->setTimezone($timeZone);

        return $dateTime->format($this->getFormat($format));
    }

    /**
     * {@inheritdoc}
     */
    public function getFormat($format)
    {
        if (!isset($this->datetimeFormats[$format])) {
            if (!isset($this->datetimeFormats[DateHelper::FORMAT_FALLBACK])) {
                Logger::w('Unknown date format ' . $format . ' for ' . __FUNCTION__, 'TAO');
                return '';
            }

            $format = DateHelper::FORMAT_FALLBACK;
        }

        return $this->datetimeFormats[$format];
    }

    /**
     * {@inheritdoc}
     */
    public function getJavascriptFormat($format)
    {
        return $this->convertPhpToJavascriptFormat($this->getFormat($format));
    }

    /**
     * Converts php DateTime format to Javascript Moment format.
     *
     * @param string $phpFormat
     *
     * @return string
     */
    public function convertPhpToJavascriptFormat($phpFormat)
    {
        // Converts all the meaningful characters.
        $replacements = self::REPLACEMENTS;

        // Converts all the escaped meaningful characters.
        foreach (self::REPLACEMENTS as $from => $to) {
            $replacements['\\' . $from] = '[' . $from . ']';
        }

        return strtr($phpFormat, $replacements);
    }
}
