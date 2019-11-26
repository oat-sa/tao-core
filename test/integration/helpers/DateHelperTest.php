<?php

declare(strict_types=1);

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
 */

namespace oat\tao\helpers\test;

use DateInterval;
use DateTime;
use oat\tao\test\TaoPhpUnitTestRunner;
use tao_helpers_Date;

class DateHelperTest extends TaoPhpUnitTestRunner
{
    /**
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testDisplayDateDefaultTimeZone(): void
    {
        $mybirthday = DateTime::createFromFormat('Y-m-d H:i:s.u', '1980-02-01 10:00:00.012345', new \DateTimeZone(\common_session_SessionManager::getSession()->getTimeZone()));

        $this->assertSame('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($mybirthday));
        $this->assertSame('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($mybirthday, tao_helpers_Date::FORMAT_LONG));
        $this->assertSame('1980-02-01 10:00', tao_helpers_Date::displayeDate($mybirthday, tao_helpers_Date::FORMAT_DATEPICKER));
        $this->assertSame('February 1, 1980, 10:00:00 am', tao_helpers_Date::displayeDate($mybirthday, tao_helpers_Date::FORMAT_VERBOSE));
        $this->assertSame('1980-02-01T10:00:00.012', tao_helpers_Date::displayeDate($mybirthday, tao_helpers_Date::FORMAT_ISO8601));

        $mybirthdayTs = $mybirthday->getTimeStamp();
        $this->assertSame('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($mybirthdayTs));
        $this->assertSame('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($mybirthdayTs, tao_helpers_Date::FORMAT_LONG));
        $this->assertSame('1980-02-01 10:00', tao_helpers_Date::displayeDate($mybirthdayTs, tao_helpers_Date::FORMAT_DATEPICKER));
        $this->assertSame('February 1, 1980, 10:00:00 am', tao_helpers_Date::displayeDate($mybirthdayTs, tao_helpers_Date::FORMAT_VERBOSE));
        $this->assertSame('1980-02-01T10:00:00.000', tao_helpers_Date::displayeDate($mybirthdayTs, tao_helpers_Date::FORMAT_ISO8601));

        $literal = new \core_kernel_classes_Literal($mybirthdayTs);
        $this->assertSame('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($literal));
        $this->assertSame('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($literal, tao_helpers_Date::FORMAT_LONG));
        $this->assertSame('1980-02-01 10:00', tao_helpers_Date::displayeDate($literal, tao_helpers_Date::FORMAT_DATEPICKER));
        $this->assertSame('February 1, 1980, 10:00:00 am', tao_helpers_Date::displayeDate($literal, tao_helpers_Date::FORMAT_VERBOSE));
        $this->assertSame('1980-02-01T10:00:00.000', tao_helpers_Date::displayeDate($literal, tao_helpers_Date::FORMAT_ISO8601));

        $ms = tao_helpers_Date::getTimeStampWithMicroseconds($mybirthday);
        $this->assertSame('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($ms));
        $this->assertSame('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($ms, tao_helpers_Date::FORMAT_LONG));
        $this->assertSame('1980-02-01 10:00', tao_helpers_Date::displayeDate($ms, tao_helpers_Date::FORMAT_DATEPICKER));
        $this->assertSame('February 1, 1980, 10:00:00 am', tao_helpers_Date::displayeDate($ms, tao_helpers_Date::FORMAT_VERBOSE));
        $this->assertSame('1980-02-01T10:00:00.012', tao_helpers_Date::displayeDate($ms, tao_helpers_Date::FORMAT_ISO8601));

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

    public function testDisplayDateSpecificTimeZone(): void
    {
        $pacific = 'Pacific/Honolulu';
        $pacificTimeZone = new \DateTimeZone($pacific);
        $mybirthday = DateTime::createFromFormat('Y-m-d H:i:s.u', '1980-02-01 10:00:00.012345', $pacificTimeZone);

        $this->assertNotSame('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($mybirthday));
        $this->assertSame('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($mybirthday, tao_helpers_Date::FORMAT_LONG, $pacificTimeZone));
        $this->assertSame('1980-02-01 10:00', tao_helpers_Date::displayeDate($mybirthday, tao_helpers_Date::FORMAT_DATEPICKER, $pacificTimeZone));
        $this->assertSame('February 1, 1980, 10:00:00 am', tao_helpers_Date::displayeDate($mybirthday, tao_helpers_Date::FORMAT_VERBOSE, $pacificTimeZone));
        $this->assertSame('1980-02-01T10:00:00.012', tao_helpers_Date::displayeDate($mybirthday, tao_helpers_Date::FORMAT_ISO8601, $pacificTimeZone));

        $mybirthdayTs = $mybirthday->getTimeStamp();
        $this->assertNotSame('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($mybirthdayTs));
        $this->assertSame('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($mybirthdayTs, tao_helpers_Date::FORMAT_LONG, $pacificTimeZone));
        $this->assertSame('1980-02-01 10:00', tao_helpers_Date::displayeDate($mybirthdayTs, tao_helpers_Date::FORMAT_DATEPICKER, $pacificTimeZone));
        $this->assertSame('February 1, 1980, 10:00:00 am', tao_helpers_Date::displayeDate($mybirthdayTs, tao_helpers_Date::FORMAT_VERBOSE, $pacificTimeZone));
        $this->assertSame('1980-02-01T10:00:00.000', tao_helpers_Date::displayeDate($mybirthdayTs, tao_helpers_Date::FORMAT_ISO8601, $pacificTimeZone));

        $literal = new \core_kernel_classes_Literal($mybirthdayTs);
        $this->assertNotSame('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($literal));
        $this->assertSame('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($literal, tao_helpers_Date::FORMAT_LONG, $pacificTimeZone));
        $this->assertSame('1980-02-01 10:00', tao_helpers_Date::displayeDate($literal, tao_helpers_Date::FORMAT_DATEPICKER, $pacificTimeZone));
        $this->assertSame('February 1, 1980, 10:00:00 am', tao_helpers_Date::displayeDate($literal, tao_helpers_Date::FORMAT_VERBOSE, $pacificTimeZone));
        $this->assertSame('1980-02-01T10:00:00.000', tao_helpers_Date::displayeDate($literal, tao_helpers_Date::FORMAT_ISO8601, $pacificTimeZone));

        $ms = tao_helpers_Date::getTimeStampWithMicroseconds($mybirthday);
        $this->assertNotSame('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($ms));
        $this->assertSame('01/02/1980 10:00:00', tao_helpers_Date::displayeDate($ms, tao_helpers_Date::FORMAT_LONG, $pacificTimeZone));
        $this->assertSame('1980-02-01 10:00', tao_helpers_Date::displayeDate($ms, tao_helpers_Date::FORMAT_DATEPICKER, $pacificTimeZone));
        $this->assertSame('February 1, 1980, 10:00:00 am', tao_helpers_Date::displayeDate($ms, tao_helpers_Date::FORMAT_VERBOSE, $pacificTimeZone));
        $this->assertSame('1980-02-01T10:00:00.012', tao_helpers_Date::displayeDate($ms, tao_helpers_Date::FORMAT_ISO8601, $pacificTimeZone));
    }

    /**
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testTestgetTimeStamp(): void
    {
        $microtime = '0.60227900 1425372507';
        $this->assertSame(1425372507, tao_helpers_Date::getTimeStamp($microtime));

        $microtime = '0.00000000 1425372507';
        $this->assertSame(1425372507.000000, tao_helpers_Date::getTimeStamp($microtime, true));
    }

    /**
     * @author Ivan Klimchuk, klimchuk@1pt.com
     */
    public function testGetTimeStampWithMicroseconds(): void
    {
        $datetime = new DateTime('2015-07-22T17:59:08.956902+0000');
        $this->assertSame('1437587948.956902', tao_helpers_Date::getTimeStampWithMicroseconds($datetime));
    }

    /**
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testDisplayInterval(): void
    {
        $now = new DateTime();

        $duration = new DateInterval('P1M'); // 1 month
        $now->add($duration);

        $this->assertSame('1 month', tao_helpers_Date::displayInterval($duration));
        $this->assertSame('1 month', tao_helpers_Date::displayInterval($duration, tao_helpers_Date::FORMAT_INTERVAL_LONG));

        $this->assertSame('1 month', tao_helpers_Date::displayInterval($duration, tao_helpers_Date::FORMAT_INTERVAL_SHORT));

        $microtime = tao_helpers_Date::getTimeStamp(microtime());

        $this->assertSame('1 hour', tao_helpers_Date::displayInterval($microtime - 3600, tao_helpers_Date::FORMAT_INTERVAL_SHORT));
        $this->assertSame('1 minute', tao_helpers_Date::displayInterval($microtime - 60, tao_helpers_Date::FORMAT_INTERVAL_SHORT));
        $this->assertSame('less than a minute', tao_helpers_Date::displayInterval($microtime - 10, tao_helpers_Date::FORMAT_INTERVAL_SHORT));

        $this->assertSame('', tao_helpers_Date::displayInterval(new \core_kernel_classes_Literal('test'), tao_helpers_Date::FORMAT_INTERVAL_SHORT));
        $this->assertSame('', tao_helpers_Date::displayInterval($microtime - 3600, 'bad format'));
    }
}
