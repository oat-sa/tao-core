<?php

error_reporting(E_ALL);

/**
 * Adapter for RDF/RDFS format
 *
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 * @subpackage helpers_data
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * This class enables you to manage interfaces with data. 
 * It provides the default prototype to adapt the data import/export from/to any
 * format.
 *
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 */
require_once('tao/helpers/data/class.GenerisAdapter.php');

/* user defined includes */
// section 127-0-1-1-32e481fe:126f616bda1:-8000:0000000000001EB0-includes begin
// section 127-0-1-1-32e481fe:126f616bda1:-8000:0000000000001EB0-includes end

/* user defined constants */
// section 127-0-1-1-32e481fe:126f616bda1:-8000:0000000000001EB0-constants begin
// section 127-0-1-1-32e481fe:126f616bda1:-8000:0000000000001EB0-constants end

/**
 * Adapter for RDF/RDFS format
 *
 * @access public
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 * @subpackage helpers_data
 */
class tao_helpers_data_GenerisAdapterRdf
    extends tao_helpers_data_GenerisAdapter
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  array options
     * @return mixed
     */
    public function __construct($options = array())
    {
        // section 127-0-1-1-32e481fe:126f616bda1:-8000:0000000000001EB2 begin
        
    	parent::__construct();
    	
        // section 127-0-1-1-32e481fe:126f616bda1:-8000:0000000000001EB2 end
    }

    /**
     * Short description of method import
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string source
     * @param  Class destination
     * @param  string namespace
     * @return boolean
     */
    public function import($source,  core_kernel_classes_Class $destination = null, $namespace = null)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-32e481fe:126f616bda1:-8000:0000000000001EBC begin
        
        $api = core_kernel_impl_ApiModelOO::singleton();
        $session = core_kernel_classes_Session::singleton();
		$localModel = $session->getNameSpace();
			
    	if(!is_null($destination) && file_exists($source)){
			
			$destModel = substr($destination->uriResource, 0, strpos($destination->uriResource, '#'));
			$returnValue = $api->importXmlRdf($destModel, $source);
		}
		else if (file_exists($source) && !is_null($namespace)){
			$returnValue = $api->importXmlRdf($namespace, $source);
		}
		else if (file_exists($source)){
			$returnValue = $api->importXmlRdf($localModel, $source);
		}
        
        // section 127-0-1-1-32e481fe:126f616bda1:-8000:0000000000001EBC end

        return (bool) $returnValue;
    }

    /**
     * Export to xml-rdf the ontology of the Class in parameter.
     * All the ontologies are exported if the class is not set
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  Class source
     * @return string
     * @see core_kernel_impl_ApiModelOO::exportXmlRdf
     */
    public function export( core_kernel_classes_Class $source = null)
    {
        $returnValue = (string) '';

        // section 127-0-1-1-32e481fe:126f616bda1:-8000:0000000000001EC1 begin
        
   		$api = core_kernel_impl_ApiModelOO::singleton();
		
		if(!is_null($source)){
			
			$session = core_kernel_classes_Session::singleton();
			$localModel = $session->getNameSpace();
			$sourceModel = substr($source->uriResource, 0, strpos($source->uriResource, '#'));
			if($localModel == $sourceModel){
				$returnValue = $api->exportXmlRdf(array($localModel));
			}
			else{
				$returnValue = $api->exportXmlRdf(array($localModel, $sourceModel));
			}
			
		}
		else{
			$returnValue = $api->exportXmlRdf();
		}
        
        // section 127-0-1-1-32e481fe:126f616bda1:-8000:0000000000001EC1 end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_data_GenerisAdapterRdf */

?>