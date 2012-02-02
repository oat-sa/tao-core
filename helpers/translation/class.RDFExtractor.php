<?php

error_reporting(E_ALL);

/**
 * TAO - tao\helpers\translation\class.RDFExtractor.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 02.02.2012, 09:43:22 with ArgoUML PHP module 
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
 * A TranslationExtractor instance extracts TranslationUnits from a given source
 * as an Item, source code, ...
 *
 * @author Jerome Bogaerts
 * @since 2.2
 * @version 1.0
 */
require_once('tao/helpers/translation/class.TranslationExtractor.php');

/* user defined includes */
// section 10-13-1-85--4f943509:1353d309872:-8000:00000000000067EB-includes begin
// section 10-13-1-85--4f943509:1353d309872:-8000:00000000000067EB-includes end

/* user defined constants */
// section 10-13-1-85--4f943509:1353d309872:-8000:00000000000067EB-constants begin
// section 10-13-1-85--4f943509:1353d309872:-8000:00000000000067EB-constants end

/**
 * Short description of class tao_helpers_translation_RDFExtractor
 *
 * @access public
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 * @subpackage helpers_translation
 */
class tao_helpers_translation_RDFExtractor
    extends tao_helpers_translation_TranslationExtractor
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method extract
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return mixed
     */
    public function extract()
    {
        // section 10-13-1-85--4f943509:1353d309872:-8000:00000000000067ED begin
        foreach ($this->getPaths() as $path){
        	// In the RDFExtractor, we expect the paths to points directly to the file.
        	if (!file_exists($path)){
        		throw new tao_helpers_translation_TranslationException("No RDF file to parse at '${path}'.");	
        	}
        	else if (!is_readable($path)){
        		throw new tao_helpers_translation_TranslationException("'${path}' is not readable. Please check file system rights.");	
        	}
        	else{
	        	try{
	        		// Try to parse the file as a DOMDocument.
	        		$doc = new DOMDocument('1.0', 'UTF-8');
	        		$doc->load(realpath($path));
	        		
	        	} catch (DOMException $e){
	        		throw new tao_helpers_translation_TranslationException("Unable to parse RDF file at '${path}'. DOM returns '" . $e->getMessage() . "'.");
	        	}	
        	}
        }
        // section 10-13-1-85--4f943509:1353d309872:-8000:00000000000067ED end
    }

} /* end of class tao_helpers_translation_RDFExtractor */

?>