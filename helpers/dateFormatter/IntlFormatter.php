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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *               
 * 
 */

namespace oat\tao\helpers\dateFormatter;

use oat\oatbox\Configurable;
use common_session_SessionManager;
use DateTimeZone;
use IntlDateFormatter;

/**
 * Utility to display dates.
 *
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 *         
 */
class IntlFormatter extends Configurable implements Formatter
{

    public function format($timestamp, $format)
    {
        $locale = common_session_SessionManager::getSession()->getInterfaceLanguage();
        $timezone = new DateTimeZone(common_session_SessionManager::getSession()->getTimeZone());
        
        switch ($format) {
        	case \tao_helpers_Date::FORMAT_LONG:
        	    $dateFormat = IntlDateFormatter::SHORT;
        	    $timeFormat = IntlDateFormatter::MEDIUM;
        	    break;
        	case \tao_helpers_Date::FORMAT_DATEPICKER:
        	    // exception
        	    $altFormatter = new EuropeanFormatter();
        	    return $altFormatter->format($timestamp, $format);
        	    /*
        	    $dateFormat = IntlDateFormatter::SHORT;
        	    $timeFormat = IntlDateFormatter::SHORT;
        	    break;
        	    */
        	case \tao_helpers_Date::FORMAT_VERBOSE:
        	    $dateFormat = IntlDateFormatter::LONG;
        	    $timeFormat = IntlDateFormatter::MEDIUM;
        	    break;
        	default:
        	    throw new common_Exception('Unexpected date format "'.$combinedFormat.'"');
        }
         
        $datefmt = new IntlDateFormatter(
            $locale,
            $dateFormat,
            $timeFormat,
            $timezone
        );
        
        return $datefmt->format($timestamp);
    }

}