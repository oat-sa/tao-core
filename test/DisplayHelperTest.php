<?php



require_once dirname(__FILE__) . '/TaoPhpUnitTestRunner.php';

/**
 * PHPUnit test of the {@link tao_helpers_Duration} helper
 * @package tao
 * @subpackage test
 */
class DisplayHelperTest extends TaoPhpUnitTestRunner {
    
    /**
     * Data provider for the testTimetoDuration method
     * @return array[] the parameters
     */
    public function stringToCleanProvider(){
        return array(
            array('This is a simple text',          '_', -1, 'This_is_a_simple_text'),
            array('This is a simple text',          '-', 10, 'This_is_a_'),
            array('@à|`',                           '-', -1, '-----'),
            array('@à|`',                           '-',  2, '--'),
            array('This 4s @ \'stronger\' tèxte',   '',  -1, 'This_4s__stronger_txte')
        );
    }
    
    /**
     * Test {@link tao_helpers_Display::}
     * @dataProvider stringToCleanProvider
     */
    public function testCleaner($input, $joker, $maxLength, $output){
        $this->assertEquals(tao_helpers_Display::textCleaner($input, $joker, $maxLength), $output); 
    }
}
?>
