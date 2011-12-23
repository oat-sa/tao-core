<?php
/**
 * This class contains some helpers in order to facilitate the creation of complex tests
 *
 * @author Joel Bout, <taosupport@tudor.lu>
 * @package tao
 * @subpackage test
 */
class TaoTestCase extends UnitTestCase {
	
	private $files = array();
	
//####################################################################################################
//####  Helpers  #####################################################################################
//####################################################################################################
	/**
	 * Create a new temporary file
	 * @param string $pContent
	 */
	public function createFile($pContent = '') {
		$tmpfname = tempnam("/tmp", "tst");
		$this->files[] = $tmpfname; 
		
		if (!empty($pContent)) {
			$handle = fopen($tmpfname, "w");
			fwrite($handle, $pContent);
			fclose($handle);
		}

		return $tmpfname;
	}
//####################################################################################################
	/**
	 * Cleanup of files
	 * @see SimpleTestCase::after()
	 */
	public function after($method) {
		parent::after($method);
		foreach ($this->files as $file) {
			unlink($file);
		}
		$this->files = array();
	}
//####################################################################################################
}
?>