<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao\models\classes\class.TaoService.php
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 14.09.2009, 15:14:23 with ArgoUML PHP module 
 * (last revised $Date: 2008-04-19 08:22:08 +0200 (Sat, 19 Apr 2008) $)
 *
 * @author Bertrand Chevrier, <taosupport@tudor.lu>
 * @package tao
 * @subpackage models_classes
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * The Service class is an abstraction of each service instance. 
 * Used to centralize the behavior related to every servcie instances.
 *
 * @author Bertrand Chevrier, <taosupport@tudor.lu>
 */
require_once('tao/models/classes/class.Service.php');

/* user defined includes */
// section 10-13-1-45-792423e0:12398d13f24:-8000:0000000000001822-includes begin
// section 10-13-1-45-792423e0:12398d13f24:-8000:0000000000001822-includes end

/* user defined constants */
// section 10-13-1-45-792423e0:12398d13f24:-8000:0000000000001822-constants begin
// section 10-13-1-45-792423e0:12398d13f24:-8000:0000000000001822-constants end

/**
 * Short description of class tao_models_classes_TaoService
 *
 * @access public
 * @author Bertrand Chevrier, <taosupport@tudor.lu>
 * @package tao
 * @subpackage models_classes
 */
class tao_models_classes_TaoService
    extends tao_models_classes_Service
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute extensions
     *
     * @access private
     * @var array
     */
    private static $extensions = array();

    // --- OPERATIONS ---

    /**
     * Get the list of TAO's children extension available in the current context
     *
     * @access public
     * @author Bertrand Chevrier, <taosupport@tudor.lu>
     * @return array
     */
    public function getLoadedExtensions()
    {
        $returnValue = array();

        // section 10-13-1-45-792423e0:12398d13f24:-8000:0000000000001820 begin
		
		if( count(self::$extensions) == 0 ){		//check it only once
			foreach(scandir($_SERVER['DOCUMENT_ROOT']) as $path){
				if( is_dir($_SERVER['DOCUMENT_ROOT'].'/'.$path) && $path != '.' && $path != '..'  && preg_match("/^tao[A-Z]+[a-z]*/", $path) ){
					self::$extensions[] = $path;
				}
			}
		}
		$returnValue = self::$extensions;
		
        // section 10-13-1-45-792423e0:12398d13f24:-8000:0000000000001820 end

        return (array) $returnValue;
    }

} /* end of class tao_models_classes_TaoService */

?>