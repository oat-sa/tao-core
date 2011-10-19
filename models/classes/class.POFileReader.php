<?php

error_reporting(E_ALL);

/**
 * An implementation of TranslationFileReader aiming at reading PO files.
 *
 * @author Jerome Bogaerts
 * @package tao
 * @since 2.2
 * @subpackage models_classes
 * @version 1.0
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * A Reading class for TranslationFiles. Must be implemented by a concrete class
 * a given Translation Format such as XLIFF, PO, ... The read method must be
 * by subclasses.
 *
 * @author Jerome Bogaerts
 * @since 2.2
 * @version 1.0
 */
require_once('tao/models/classes/class.TranslationFileReader.php');

/* user defined includes */
// section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034C8-includes begin
// section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034C8-includes end

/* user defined constants */
// section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034C8-constants begin
// section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034C8-constants end

/**
 * An implementation of TranslationFileReader aiming at reading PO files.
 *
 * @access public
 * @author Jerome Bogaerts
 * @package tao
 * @since 2.2
 * @subpackage models_classes
 * @version 1.0
 */
class tao_models_classes_POFileReader
    extends tao_models_classes_TranslationFileReader
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method read
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function read()
    {
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034CA begin
        $file = $this->getFilePath();
        if (!file_exists($file)) {
			throw new tao_models_classes_TranslationException("The translation file '${file}' does not exist.");
		}
		
		$fc = implode('',file($file));
		
		$matched = preg_match_all('/(msgid\s+("([^"]|\\\\")*?"\s*)+)\s+' .
								  '(msgstr\s+("([^"]|\\\\")*?(?<!\\\)"\s*)+)/',
								  $fc, $matches);
		
		if (!$matched) {
			$res = array();
		}
		else {
			$res = array();
			
			for ($i=0; $i<$matched; $i++) {
				$msgid = preg_replace('/\s*msgid\s*"(.*)"\s*/s','\\1',$matches[1][$i]);
				$msgstr= preg_replace('/\s*msgstr\s*"(.*)"\s*/s','\\1',$matches[4][$i]);
				
				$msgstr = $this->poString($msgstr);
				
				if ($msgstr) {
					$res[$this->poString($msgid)] = $msgstr;
				}
				else {
					$res[$this->poString($msgid)] = '';
				}
			}
			
			if (!empty($res[''])) {
				$meta = $res[''];
				unset($res['']);
			}
		}
		
		// Create the translation file.
		$tf = new tao_models_classes_TranslationFile();
		foreach ($res as $msgid => $msgstr) {
			$tu = new tao_models_classes_TranslationUnit($msgid, $msgstr);
			$tf->addTranslationUnit($tu);
		}
		
		$this->setTranslationFile($tf);
		var_dump($tf);
		
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034CA end
    }

    /**
     * Utility method to sanitize/unsanitze a PO string.
     *
     * @access private
     * @author firstname and lastname of author, <author@example.org>
     * @param  string string
     * @param  boolean reverse
     * @return string
     */
    private function poString($string, $reverse = false)
    {
        $returnValue = (string) '';

        // section 10-13-1-85-4a5a42ca:1331c4d8dc7:-8000:0000000000003527 begin
	    if ($reverse) {
			$smap = array('"', "\n", "\t", "\r");
			$rmap = array('\\"', '\\n"' . "\n" . '"', '\\t', '\\r');
			$returnValue = trim((string) str_replace($smap, $rmap, $string));
		} else {
			$smap = array('/"\s+"/', '/\\\\n/', '/\\\\r/', '/\\\\t/', '/\\\"/');
			$rmap = array('', "\n", "\r", "\t", '"');
			$returnValue = trim((string) preg_replace($smap, $rmap, $string));
		}
        // section 10-13-1-85-4a5a42ca:1331c4d8dc7:-8000:0000000000003527 end

        return (string) $returnValue;
    }

} /* end of class tao_models_classes_POFileReader */

?>