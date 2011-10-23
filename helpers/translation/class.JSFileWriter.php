<?php

error_reporting(E_ALL);

/**
 * An implementation of TranslationFileWriter aiming at writing JavaScript
 * files.
 *
 * @author Jerome Bogaerts
 * @package tao
 * @since 2.2
 * @subpackage helpers_translation
 * @version 1.0
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * A Writing class for TranslationFiles. Must be implemented by a concrete class
 * a given Translation Format such as XLIFF, PO, ... The write method must be
 * by subclasses.
 *
 * @author Jerome Bogaerts
 * @since 2.2
 * @version 1.0
 */
require_once('tao/helpers/translation/class.TranslationFileWriter.php');

/* user defined includes */
// section -64--88-1-7-60bf53a7:13332a8c400:-8000:00000000000031D0-includes begin
// section -64--88-1-7-60bf53a7:13332a8c400:-8000:00000000000031D0-includes end

/* user defined constants */
// section -64--88-1-7-60bf53a7:13332a8c400:-8000:00000000000031D0-constants begin
// section -64--88-1-7-60bf53a7:13332a8c400:-8000:00000000000031D0-constants end

/**
 * An implementation of TranslationFileWriter aiming at writing JavaScript
 * files.
 *
 * @access public
 * @author Jerome Bogaerts
 * @package tao
 * @since 2.2
 * @subpackage helpers_translation
 * @version 1.0
 */
class tao_helpers_translation_JSFileWriter
    extends tao_helpers_translation_TranslationFileWriter
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method write
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function write()
    {
        // section -64--88-1-7-3ec47102:13332ada7cb:-8000:00000000000031DE begin
        $path = $this->getFilePath();
        $buffer = '';
        $langCode = $this->getTranslationFile()->getTargetLanguage();
        $strings = array();
        
        foreach ($this->getTranslationFile()->getTranslationUnits() as $tu) {
        	$strings[$tu->getSource()] = $tu->getTarget();
        }
        
		$buffer  = "/* auto generated content */\n";
		$buffer .= "/* extension: unknown, lang: $langCode */\n";
		$buffer .= "var langCode = '$langCode';\n";
		$buffer .= "var i18n_tr = " . json_encode($strings) . ";";
		if(!file_put_contents($path, $buffer)){
			throw new tao_helpers_translation_TranslationException("An error occured while writing Javascript " .
																   "translation file '${path}'.");
		}
        
        // section -64--88-1-7-3ec47102:13332ada7cb:-8000:00000000000031DE end
    }

} /* end of class tao_helpers_translation_JSFileWriter */

?>