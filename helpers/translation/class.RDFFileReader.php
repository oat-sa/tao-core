<?php

error_reporting(E_ALL);

/**
 * An implementation of TranslationFileReader aiming at reading RDF Translation
 *
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 * @subpackage helpers_translation
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
require_once('tao/helpers/translation/class.TranslationFileReader.php');

/* user defined includes */
// section -64--88-56-1--508090e5:136c5d740c9:-8000:0000000000003920-includes begin
// section -64--88-56-1--508090e5:136c5d740c9:-8000:0000000000003920-includes end

/* user defined constants */
// section -64--88-56-1--508090e5:136c5d740c9:-8000:0000000000003920-constants begin
// section -64--88-56-1--508090e5:136c5d740c9:-8000:0000000000003920-constants end

/**
 * An implementation of TranslationFileReader aiming at reading RDF Translation
 *
 * @access public
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 * @subpackage helpers_translation
 */
class tao_helpers_translation_RDFFileReader
    extends tao_helpers_translation_TranslationFileReader
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method read
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return mixed
     */
    public function read()
    {
        // section -64--88-56-1--508090e5:136c5d740c9:-8000:0000000000003922 begin
        // section -64--88-56-1--508090e5:136c5d740c9:-8000:0000000000003922 end
    }

} /* end of class tao_helpers_translation_RDFFileReader */

?>