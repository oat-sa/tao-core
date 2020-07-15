<?php


namespace oat\tao\test;

use oat\generis\test\TestCase;

class CalendarTest extends TestCase
{
    public function testCalendarExtension()
    {
        $this->assertTrue(function_exists('cal_days_in_month'));
    }
}