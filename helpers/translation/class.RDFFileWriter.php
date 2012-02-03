<?php

error_reporting(E_ALL);

/**
 * A FileWriter aiming at writing RDF files.
 *
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 * @subpackage helpers_translation
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
// section 10-13-1-85--345dcc7e:13543c6ca3a:-8000:0000000000003A84-includes begin
// section 10-13-1-85--345dcc7e:13543c6ca3a:-8000:0000000000003A84-includes end

/* user defined constants */
// section 10-13-1-85--345dcc7e:13543c6ca3a:-8000:0000000000003A84-constants begin
// section 10-13-1-85--345dcc7e:13543c6ca3a:-8000:0000000000003A84-constants end

/**
 * A FileWriter aiming at writing RDF files.
 *
 * @access public
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 * @subpackage helpers_translation
 */
class tao_helpers_translation_RDFFileWriter
    extends tao_helpers_translation_TranslationFileWriter
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Writes the RDF file on the file system.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return mixed
     */
    public function write()
    {
        // section 10-13-1-85--345dcc7e:13543c6ca3a:-8000:0000000000003A86 begin
        // section 10-13-1-85--345dcc7e:13543c6ca3a:-8000:0000000000003A86 end
    }

} /* end of class tao_helpers_translation_RDFFileWriter */

?>