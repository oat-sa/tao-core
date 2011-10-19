<?php

error_reporting(E_ALL);

/**
 * An implementation of TranslationFileWriter aiming at writing PO files.
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
 * A Writing class for TranslationFiles. Must be implemented by a concrete class
 * a given Translation Format such as XLIFF, PO, ... The write method must be
 * by subclasses.
 *
 * @author Jerome Bogaerts
 * @since 2.2
 * @version 1.0
 */
require_once('tao/models/classes/class.TranslationFileWriter.php');

/* user defined includes */
// section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034E1-includes begin
// section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034E1-includes end

/* user defined constants */
// section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034E1-constants begin
// section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034E1-constants end

/**
 * An implementation of TranslationFileWriter aiming at writing PO files.
 *
 * @access public
 * @author Jerome Bogaerts
 * @package tao
 * @since 2.2
 * @subpackage models_classes
 * @version 1.0
 */
class tao_models_classes_POFileWriter
    extends tao_models_classes_TranslationFileWriter
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
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034E3 begin
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034E3 end
    }

} /* end of class tao_models_classes_POFileWriter */

?>