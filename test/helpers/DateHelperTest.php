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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */
namespace oat\tao\helpers\test;

use oat\tao\helpers\dateFormatter\EuropeanFormatter;
use oat\tao\test\TaoPhpUnitTestRunner;
use tao_helpers_Date;
use DateInterval;
use DateTime;

require_once __DIR__.'/../../includes/raw_start.php';
class DateHelperTest extends TaoPhpUnitTestRunner
{


    public function testGetISO8601Offset(){
        $this->assertEquals('+0000', EuropeanFormatter::getISO8601Offset(0));
        $this->assertEquals('-0400', EuropeanFormatter::getISO8601Offset(-14400));
        $this->assertEquals('-1200', EuropeanFormatter::getISO8601Offset(-43200));
        $this->assertEquals('+0100', EuropeanFormatter::getISO8601Offset(3600));
        $this->assertEquals('+1000', EuropeanFormatter::getISO8601Offset(36000));
        $this->assertEquals('+0530', EuropeanFormatter::getISO8601Offset(19800));
    }


    /**
     * 
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testDisplayDate()
    {
        $mybirthday = DateTime::createFromFormat('Y-m-d H:i:s.u', '1980-02-01 10:00:00.012345', new \DateTimeZone(\common_session_SessionManager::getSession()->getTimeZone()));

        $offset = $mybirthday->getOffset();
        $offset = EuropeanFormatter::getISO8601Offset($offset);

        $this->assertEquals('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($mybirthday));
        $this->assertEquals('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($mybirthday, tao_helpers_Date::FORMAT_LONG));
        $this->assertEquals('1980-02-01 10:00', tao_helpers_Date::displayeDate($mybirthday, tao_helpers_Date::FORMAT_DATEPICKER));
        $this->assertEquals('February 1, 1980, 10:00:00 am', tao_helpers_Date::displayeDate($mybirthday, tao_helpers_Date::FORMAT_VERBOSE));
        $this->assertEquals('1980-02-01T10:00:00.012'.$offset, tao_helpers_Date::displayeDate($mybirthday, tao_helpers_Date::FORMAT_ISO8601));
        
        $mybirthdayTs = $mybirthday->getTimeStamp();
        $this->assertEquals('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($mybirthdayTs));
        $this->assertEquals('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($mybirthdayTs, tao_helpers_Date::FORMAT_LONG));
        $this->assertEquals('1980-02-01 10:00', tao_helpers_Date::displayeDate($mybirthdayTs, tao_helpers_Date::FORMAT_DATEPICKER));
        $this->assertEquals('February 1, 1980, 10:00:00 am', tao_helpers_Date::displayeDate($mybirthdayTs, tao_helpers_Date::FORMAT_VERBOSE));
        $this->assertEquals('1980-02-01T10:00:00.000'.$offset, tao_helpers_Date::displayeDate($mybirthdayTs, tao_helpers_Date::FORMAT_ISO8601));
        
        $literal = new \core_kernel_classes_Literal($mybirthdayTs);
        $this->assertEquals('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($literal));
        $this->assertEquals('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($literal, tao_helpers_Date::FORMAT_LONG));
        $this->assertEquals('1980-02-01 10:00', tao_helpers_Date::displayeDate($literal, tao_helpers_Date::FORMAT_DATEPICKER));
        $this->assertEquals('February 1, 1980, 10:00:00 am', tao_helpers_Date::displayeDate($literal, tao_helpers_Date::FORMAT_VERBOSE));
        $this->assertEquals('1980-02-01T10:00:00.000'.$offset, tao_helpers_Date::displayeDate($literal, tao_helpers_Date::FORMAT_ISO8601));

        $ms = tao_helpers_Date::getTimeStampWithMicroseconds($mybirthday);
        $this->assertEquals('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($ms));
        $this->assertEquals('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($ms, tao_helpers_Date::FORMAT_LONG));
        $this->assertEquals('1980-02-01 10:00', tao_helpers_Date::displayeDate($ms, tao_helpers_Date::FORMAT_DATEPICKER));
        $this->assertEquals('February 1, 1980, 10:00:00 am', tao_helpers_Date::displayeDate($ms, tao_helpers_Date::FORMAT_VERBOSE));
        $this->assertEquals('1980-02-01T10:00:00.012'.$offset, tao_helpers_Date::displayeDate($ms, tao_helpers_Date::FORMAT_ISO8601));
    }

    /**
     * 
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testgetTimeStamp()
    {
        $microtime = "0.60227900 1425372507";
        $this->assertEquals(1425372507, tao_helpers_Date::getTimeStamp($microtime));
    }

    /**
     *
     * @author Ivan Klimchuk, klimchuk@1pt.com
     */
    public function testGetTimeStampWithMicroseconds()
    {
        $datetime = new DateTime('2015-07-22T17:59:08.956902+0000');
        $this->assertEquals('1437587948.956902', tao_helpers_Date::getTimeStampWithMicroseconds($datetime));
    }

    /**
     * 
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testDisplayInterval()
    {
        $now = new DateTime();
        
        $duration = new DateInterval('P1M'); // 1 month
        $now->add($duration);
        
        $this->assertEquals('1 month', tao_helpers_Date::displayInterval($duration));
        $this->assertEquals('1 month', tao_helpers_Date::displayInterval($duration, tao_helpers_Date::FORMAT_INTERVAL_LONG));
        
        $this->assertEquals('1 month', tao_helpers_Date::displayInterval($duration, tao_helpers_Date::FORMAT_INTERVAL_SHORT));
        
        $microtime = tao_helpers_Date::getTimeStamp(microtime());
        
        $this->assertEquals('1 hour', tao_helpers_Date::displayInterval($microtime - 3600, tao_helpers_Date::FORMAT_INTERVAL_SHORT));
        $this->assertEquals('1 minute', tao_helpers_Date::displayInterval($microtime - 60, tao_helpers_Date::FORMAT_INTERVAL_SHORT));
        $this->assertEquals('less than a minute', tao_helpers_Date::displayInterval($microtime - 10, tao_helpers_Date::FORMAT_INTERVAL_SHORT));
        
        $this->assertEquals('', tao_helpers_Date::displayInterval(new \core_kernel_classes_Literal('test'), tao_helpers_Date::FORMAT_INTERVAL_SHORT));
        $this->assertEquals('', tao_helpers_Date::displayInterval($microtime - 3600, 'bad format'));
    }
}
