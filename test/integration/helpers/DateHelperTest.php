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
namespace oat\tao\test\unit\helpers;

use oat\generis\test\TestCase;
use tao_helpers_Date;
use DateInterval;
use DateTime;

class DateHelperTest extends TestCase
{
    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testDisplayDateDefaultTimeZone()
    {
        $mybirthday = DateTime::createFromFormat('Y-m-d H:i:s.u', '1980-02-01 10:00:00.012345', new \DateTimeZone(\common_session_SessionManager::getSession()->getTimeZone()));

        $this->assertEquals('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($mybirthday));
        $this->assertEquals('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($mybirthday, tao_helpers_Date::FORMAT_LONG));
        $this->assertEquals('1980-02-01 10:00', tao_helpers_Date::displayeDate($mybirthday, tao_helpers_Date::FORMAT_DATEPICKER));
        $this->assertEquals('February 1, 1980, 10:00:00 am', tao_helpers_Date::displayeDate($mybirthday, tao_helpers_Date::FORMAT_VERBOSE));
        $this->assertEquals('1980-02-01T10:00:00.012', tao_helpers_Date::displayeDate($mybirthday, tao_helpers_Date::FORMAT_ISO8601));

        $mybirthdayTs = $mybirthday->getTimeStamp();
        $this->assertEquals('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($mybirthdayTs));
        $this->assertEquals('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($mybirthdayTs, tao_helpers_Date::FORMAT_LONG));
        $this->assertEquals('1980-02-01 10:00', tao_helpers_Date::displayeDate($mybirthdayTs, tao_helpers_Date::FORMAT_DATEPICKER));
        $this->assertEquals('February 1, 1980, 10:00:00 am', tao_helpers_Date::displayeDate($mybirthdayTs, tao_helpers_Date::FORMAT_VERBOSE));
        $this->assertEquals('1980-02-01T10:00:00.000', tao_helpers_Date::displayeDate($mybirthdayTs, tao_helpers_Date::FORMAT_ISO8601));

        $literal = new \core_kernel_classes_Literal($mybirthdayTs);
        $this->assertEquals('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($literal));
        $this->assertEquals('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($literal, tao_helpers_Date::FORMAT_LONG));
        $this->assertEquals('1980-02-01 10:00', tao_helpers_Date::displayeDate($literal, tao_helpers_Date::FORMAT_DATEPICKER));
        $this->assertEquals('February 1, 1980, 10:00:00 am', tao_helpers_Date::displayeDate($literal, tao_helpers_Date::FORMAT_VERBOSE));
        $this->assertEquals('1980-02-01T10:00:00.000', tao_helpers_Date::displayeDate($literal, tao_helpers_Date::FORMAT_ISO8601));

        $ms = tao_helpers_Date::getTimeStampWithMicroseconds($mybirthday);
        $this->assertEquals('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($ms));
        $this->assertEquals('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($ms, tao_helpers_Date::FORMAT_LONG));
        $this->assertEquals('1980-02-01 10:00', tao_helpers_Date::displayeDate($ms, tao_helpers_Date::FORMAT_DATEPICKER));
        $this->assertEquals('February 1, 1980, 10:00:00 am', tao_helpers_Date::displayeDate($ms, tao_helpers_Date::FORMAT_VERBOSE));
        $this->assertEquals('1980-02-01T10:00:00.012', tao_helpers_Date::displayeDate($ms, tao_helpers_Date::FORMAT_ISO8601));

        $ms = '0.9999 1509450498';
        $dateStr = tao_helpers_Date::displayeDate($ms, tao_helpers_Date::FORMAT_ISO8601);
        $this->assertSame(1, preg_match('/\.000$/', $dateStr));

        $ms = '0.99999999 1509450498';
        $dateStr = tao_helpers_Date::displayeDate($ms, tao_helpers_Date::FORMAT_ISO8601);
        $this->assertSame(1, preg_match('/\.000$/', $dateStr));

        $ms = '0.99999951 1509450498';
        $dateStr = tao_helpers_Date::displayeDate($ms, tao_helpers_Date::FORMAT_ISO8601);
        $this->assertSame(1, preg_match('/\.000$/', $dateStr));
    }

    public function testDisplayDateSpecificTimeZone()
    {
        $pacific = 'Pacific/Honolulu';
        $pacificTimeZone = new \DateTimeZone($pacific);
        $mybirthday = DateTime::createFromFormat('Y-m-d H:i:s.u', '1980-02-01 10:00:00.012345', $pacificTimeZone);

        $this->assertNotEquals('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($mybirthday));
        $this->assertEquals('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($mybirthday, tao_helpers_Date::FORMAT_LONG, $pacificTimeZone));
        $this->assertEquals('1980-02-01 10:00', tao_helpers_Date::displayeDate($mybirthday, tao_helpers_Date::FORMAT_DATEPICKER, $pacificTimeZone));
        $this->assertEquals('February 1, 1980, 10:00:00 am', tao_helpers_Date::displayeDate($mybirthday, tao_helpers_Date::FORMAT_VERBOSE, $pacificTimeZone));
        $this->assertEquals('1980-02-01T10:00:00.012', tao_helpers_Date::displayeDate($mybirthday, tao_helpers_Date::FORMAT_ISO8601, $pacificTimeZone));

        $mybirthdayTs = $mybirthday->getTimeStamp();
        $this->assertNotEquals('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($mybirthdayTs));
        $this->assertEquals('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($mybirthdayTs, tao_helpers_Date::FORMAT_LONG, $pacificTimeZone));
        $this->assertEquals('1980-02-01 10:00', tao_helpers_Date::displayeDate($mybirthdayTs, tao_helpers_Date::FORMAT_DATEPICKER, $pacificTimeZone));
        $this->assertEquals('February 1, 1980, 10:00:00 am', tao_helpers_Date::displayeDate($mybirthdayTs, tao_helpers_Date::FORMAT_VERBOSE, $pacificTimeZone));
        $this->assertEquals('1980-02-01T10:00:00.000', tao_helpers_Date::displayeDate($mybirthdayTs, tao_helpers_Date::FORMAT_ISO8601, $pacificTimeZone));

        $literal = new \core_kernel_classes_Literal($mybirthdayTs);
        $this->assertNotEquals('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($literal));
        $this->assertEquals('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($literal, tao_helpers_Date::FORMAT_LONG, $pacificTimeZone));
        $this->assertEquals('1980-02-01 10:00', tao_helpers_Date::displayeDate($literal, tao_helpers_Date::FORMAT_DATEPICKER, $pacificTimeZone));
        $this->assertEquals('February 1, 1980, 10:00:00 am', tao_helpers_Date::displayeDate($literal, tao_helpers_Date::FORMAT_VERBOSE, $pacificTimeZone));
        $this->assertEquals('1980-02-01T10:00:00.000', tao_helpers_Date::displayeDate($literal, tao_helpers_Date::FORMAT_ISO8601, $pacificTimeZone));

        $ms = tao_helpers_Date::getTimeStampWithMicroseconds($mybirthday);
        $this->assertNotEquals('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($ms));
        $this->assertEquals('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($ms, tao_helpers_Date::FORMAT_LONG, $pacificTimeZone));
        $this->assertEquals('1980-02-01 10:00', tao_helpers_Date::displayeDate($ms, tao_helpers_Date::FORMAT_DATEPICKER, $pacificTimeZone));
        $this->assertEquals('February 1, 1980, 10:00:00 am', tao_helpers_Date::displayeDate($ms, tao_helpers_Date::FORMAT_VERBOSE, $pacificTimeZone));
        $this->assertEquals('1980-02-01T10:00:00.012', tao_helpers_Date::displayeDate($ms, tao_helpers_Date::FORMAT_ISO8601, $pacificTimeZone));
    }
}
