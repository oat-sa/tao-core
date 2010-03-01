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
		
		$modelId = 0;
		if(!is_null($source)){
			$dbWrapper =  core_kernel_classes_DbWrapper::singleton(DATABASE_NAME);
			
			$result = $dbWrapper->execSql(
				"SELECT `statements`.`modelID`, `models`.`modelURI` FROM `statements` 
				 INNER JOIN `models` ON `models`.`modelID` = `statements`.`modelID`  
				 WHERE `statements`.`subject` = '{$source->uriResource}' AND predicate = 'http://www.w3.org/2000/01/rdf-schema#subClassOf'"
			);
			if (!$result->EOF){
				$modelId 	= $result->fields['modelID'];
				$modelUri 	= $result->fields['modelURI'];
			}
			
			if($modelId > 0){
				try{
					$namespaces = array(
						'xmlns:rdf'		=> 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
						'xmlns:rdfs'	=> 'http://www.w3.org/2000/01/rdf-schema#',
						'xmlns:ns1'	=> $modelUri,
						'xml:base'		=> $modelUri
					);
					
					$dom = new DOMDocument();
					$root = $dom->createElement('rdf:RDF');
					foreach($namespaces as $namespaceId => $namespaceUri){
						$root->setAttribute($namespaceId, $namespaceUri);
					}
					$dom->appendChild($root);
					
					$subjects = array();
					$result = $dbWrapper->execSql("SELECT DISTINCT `subject` FROM `statements` WHERE `modelID`=$modelId");
					while(!$result->EOF){
						$subjects[] = $result->fields['subject'];
						$result->moveNext();
					}
					foreach($subjects as $subject){
						$description = $dom->createElement('rdf:Description');
						$description->setAttribute('rdf:ID', str_replace($modelUri, '', $subject));
						
							$result = $dbWrapper->execSql("SELECT * FROM `statements` WHERE `modelID`=$modelId AND `subject`= '{$subject}'");
							while(!$result->EOF){
								
								$predicate = trim($result->fields['predicate']);
								$object = trim($result->fields['object']);
								
								$nodeName = null;
								
								foreach($namespaces as $namespaceId => $namespaceUri){
									if(preg_match("/^".preg_quote($namespaceUri, '/')."/", $predicate)){
										if($namespaceId == 'xml:base'){
											$nodeName = str_replace($namespaceUri, '', $predicate);
											break;
										}
										else{
											$nodeName = str_replace('xmlns:', '', $namespaceId).':'.str_replace($namespaceUri, '', $predicate);
											break;
										}
									}
								}
								$resourceValue = false;
								$resourceNS = null;
								foreach($namespaces as $namespaceId => $namespaceUri){
									if(preg_match("/^".preg_quote($namespaceUri, '/')."/", $object)){
										$resourceValue = true;
										if($namespaceId != 'xml:base'){
											$resourceNS = $namespaceUri;
										}
										break;
									}
									else if(preg_match("/http\:\/\/(.*)\#[a-zA-Z1-9]*/", $object)){
										$resourceValue = true;
									}
								}
								if(!is_null($nodeName)){
									try{
										$node = $dom->createElement($nodeName);
										if($resourceValue){
											if(is_null($resourceNS)){
												$node->setAttribute('rdf:resource', str_replace($modelUri, '', $object));
											}
											else{
												$node->setAttribute('rdf:resource', $object);
											}
										}
										else{
											if(!empty($object) && !is_null($object)){
												$node->appendChild($dom->createCDATASection($object));
											}
										}
										$description->appendChild($node);
									}
									catch(DOMException $de){
										//print $de;
									}
								}
								$result->moveNext();
							}
						$root->appendChild($description);
					}
					
					$returnValue = $dom->saveXml();
				}
				catch(Exception $e){
					print $e;
				}
			}
		}
		
        // section 127-0-1-1-32e481fe:126f616bda1:-8000:0000000000001EC1 end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_GenerisDataAdapterRdf */

?>