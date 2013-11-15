<?php
require_once dirname(__FILE__) . '/TaoPhpUnitTestRunner.php';

/**
 * PHPUnit test of the {@link tao_helpers_Duration} helper
 * @package tao
 * @subpackage test
 */
class DurationTest extends TaoPhpUnitTestRunner {
    
    /**
     * Data provider for the testTimetoDuration method
     * @return array[] the parameters
     */
    public function timetoDurationProvider(){
        return array(
            array('00:00:00', 'PT0H0M0S'),
            array('01:34:28', 'PT1H34M28S'),
            array('', 'PT0S'),
            array(null, 'PT0S')
        );
    }
    
    /**
     * Test {@link tao_helpers_Duration::timetoDuration}
     * @dataProvider timetoDurationProvider
     * @param string $time the parameter of timetoDuration
     * @param string $expected the expected result
     */
    public function testTimetoDuration($time, $expected){
        $result = tao_helpers_Duration::timetoDuration($time);
        $this->assertEquals($expected, $result);
    }
    
    
    /**
     * Data provider for the testIntervalToTime method
     * @return array[] the parameters
     */
    public function intervalToTimeProvider(){
        return array(
            array(new DateInterval('PT0H0M0S'), '00:00:00'),
            array(new DateInterval('PT1H34M28S'), '01:34:28')
        );
    }
    
    /**
     * Test {@link tao_helpers_Duration::intervalToTime}
     * @dataProvider intervalToTimeProvider
     * @param string $time the parameter of intervalToTime
     * @param string $expected the expected result
     */
    public function testIntervalToTime($interval, $expected){
        $result = tao_helpers_Duration::intervalToTime($interval);
        $this->assertEquals($expected, $result);
    }
        
    
    /**
     * Data provider for the testDurationToTime method
     * @return array[] the parameters
     */
    public function durationToTimeProvider(){
        return array(
            array('PT0H0M0S', '00:00:00'),
            array('PT1H34M28S', '01:34:28'),
            array('', null),
            array(null, null)
        );
    }
    
    /**
     * Test {@link tao_helpers_Duration::durationToTime}
     * @dataProvider durationToTimeProvider
     * @param string $duration the parameter of durationToTime
     * @param string $expected the expected result
     */
    public function testDurationToTime($duration, $expected){
        $result = tao_helpers_Duration::durationToTime($duration);
        $this->assertEquals($expected, $result);
    }
}
?>
