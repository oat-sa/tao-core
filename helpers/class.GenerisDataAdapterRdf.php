<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/helpers/class.GenerisDataAdapterRdf.php
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 22.02.2010, 15:39:02 with ArgoUML PHP module 
 * (last revised $Date: 2009-04-11 21:57:46 +0200 (Sat, 11 Apr 2009) $)
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_helpers_GenerisDataAdapter
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
require_once('tao/helpers/class.GenerisDataAdapter.php');

/* user defined includes */
// section 127-0-1-1-32e481fe:126f616bda1:-8000:0000000000001EB0-includes begin
// section 127-0-1-1-32e481fe:126f616bda1:-8000:0000000000001EB0-includes end

/* user defined constants */
// section 127-0-1-1-32e481fe:126f616bda1:-8000:0000000000001EB0-constants begin
// section 127-0-1-1-32e481fe:126f616bda1:-8000:0000000000001EB0-constants end

/**
 * Short description of class tao_helpers_GenerisDataAdapterRdf
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers
 */
class tao_helpers_GenerisDataAdapterRdf
    extends tao_helpers_GenerisDataAdapter
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  array options
     * @return mixed
     */
    public function __construct($options = array())
    {
        // section 127-0-1-1-32e481fe:126f616bda1:-8000:0000000000001EB2 begin
        // section 127-0-1-1-32e481fe:126f616bda1:-8000:0000000000001EB2 end
    }

    /**
     * Short description of method import
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string source
     * @param  Class destination
     * @return boolean
     */
    public function import($source,  core_kernel_classes_Class $destination)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-32e481fe:126f616bda1:-8000:0000000000001EBC begin
		
		
		
        // section 127-0-1-1-32e481fe:126f616bda1:-8000:0000000000001EBC end

        return (bool) $returnValue;
    }

    /**
     * Short description of method export
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Class source
     * @return string
     */
    public function export( core_kernel_classes_Class $source)
    {
        $returnValue = (string) '';

        // section 127-0-1-1-32e481fe:126f616bda1:-8000:0000000000001EC1 begin
		
		
		if(!is_null($source)){
			$dbWrapper =  core_kernel_classes_DbWrapper::singleton(DATABASE_NAME);
			
			$result = $dbWrapper->execSql(
				"SELECT `statements`.`modelID`, `models`.`modelURI` FROM `statements` 
				 INNER JOIN `models` ON `models`.`modelID` = `statements`.`modelID`  
				 WHERE `statements`.`subject` = '{$source->uriResource}' AND predicate = 'http://www.w3.org/2000/01/rdf-schema#subClassOf'"
			);
			if (!$result->EOF){
				$modelUri 	= $result->fields['modelURI'];
			}
			
			$api = core_kernel_classes_ApiModelOO::singleton();
			$returnValue = $api->exportXmlRdf($modelUri);
		}
		else{
			$returnValue = $api->exportXmlRdf();
		}
		
        // section 127-0-1-1-32e481fe:126f616bda1:-8000:0000000000001EC1 end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_GenerisDataAdapterRdf */

?>