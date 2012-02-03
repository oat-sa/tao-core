<?php

error_reporting(E_ALL);

/**
 * TAO - tao\helpers\translation\class.POFile.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 03.02.2012, 16:25:41 with ArgoUML PHP module 
 * (last revised $Date: 2008-04-19 08:22:08 +0200 (Sat, 19 Apr 2008) $)
 *
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 * @subpackage helpers_translation
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * A TranslationFile aiming at translating a TAO Component
 *
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 */
require_once('tao/helpers/translation/class.TaoTranslationFile.php');

/* user defined includes */
// section 10-13-1-85-73c9aa2d:134d14a8b30:-8000:00000000000038C6-includes begin
// section 10-13-1-85-73c9aa2d:134d14a8b30:-8000:00000000000038C6-includes end

/* user defined constants */
// section 10-13-1-85-73c9aa2d:134d14a8b30:-8000:00000000000038C6-constants begin
// section 10-13-1-85-73c9aa2d:134d14a8b30:-8000:00000000000038C6-constants end

/**
 * Short description of class tao_helpers_translation_POFile
 *
 * @access public
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 * @subpackage helpers_translation
 */
class tao_helpers_translation_POFile
    extends tao_helpers_translation_TaoTranslationFile
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute headers
     *
     * @access private
     * @var array
     */
    private $headers = array();

    // --- OPERATIONS ---

    /**
     * Short description of method addHeader
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string name
     * @param  string value
     * @return void
     */
    public function addHeader($name, $value)
    {
        // section 10-13-1-85-73c9aa2d:134d14a8b30:-8000:00000000000038DA begin
        $this->headers[$name] = $value;
        // section 10-13-1-85-73c9aa2d:134d14a8b30:-8000:00000000000038DA end
    }

    /**
     * Short description of method removeHeader
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string name
     * @return void
     */
    public function removeHeader($name)
    {
        // section 10-13-1-85-73c9aa2d:134d14a8b30:-8000:00000000000038DE begin
        unset($this->headers[$name]);
        // section 10-13-1-85-73c9aa2d:134d14a8b30:-8000:00000000000038DE end
    }

    /**
     * Short description of method getHeaders
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return array
     */
    public function getHeaders()
    {
        $returnValue = array();

        // section 10-13-1-85--53f40a93:134d16b5c93:-8000:00000000000038E1 begin
        $returnValue = $this->headers;
        // section 10-13-1-85--53f40a93:134d16b5c93:-8000:00000000000038E1 end

        return (array) $returnValue;
    }

} /* end of class tao_helpers_translation_POFile */

?>