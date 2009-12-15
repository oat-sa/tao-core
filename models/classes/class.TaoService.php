<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/models/classes/class.TaoService.php
 *
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 15.10.2009, 14:57:06 with ArgoUML PHP module 
 * (last revised $Date: 2008-04-19 08:22:08 +0200 (Sat, 19 Apr 2008) $)
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
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
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
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
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
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

    /**
     * Short description of attribute structure
     *
     * @access protected
     * @var array
     */
    protected static $structure = array();

    // --- OPERATIONS ---

    /**
     * Get the list of TAO's children extension available in the current context
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return array
     */
    public function getLoadedExtensions()
    {
        $returnValue = array();

        // section 10-13-1-45-792423e0:12398d13f24:-8000:0000000000001820 begin
		
		if( count(self::$extensions) == 0 ){		//check it only once
			foreach(scandir(ROOT_PATH) as $path){
				if( is_dir(ROOT_PATH.'/'.$path) && $path != '.' && $path != '..'  && preg_match("/^tao[A-Z]+[a-z]*/", $path) ){
					self::$extensions[] = $path;
				}
			}
		}
		$returnValue = self::$extensions;
		
        // section 10-13-1-45-792423e0:12398d13f24:-8000:0000000000001820 end

        return (array) $returnValue;
    }

    /**
     * Short description of method isLoaded
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  string extension
     * @return boolean
     */
    public function isLoaded($extension)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-5f1894ad:12457319d43:-8000:0000000000001A6F begin
		(in_array($extension, $this->getLoadedExtensions())) ? $returnValue = true : $returnValue = false;
        // section 127-0-1-1-5f1894ad:12457319d43:-8000:0000000000001A6F end

        return (bool) $returnValue;
    }

    /**
     * Short description of method setCurrentExtension
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  string extension
     * @return mixed
     */
    public function setCurrentExtension($extension)
    {
        // section 127-0-1-1-5f1894ad:12457319d43:-8000:0000000000001A66 begin
		if(!$this->isLoaded($extension)){
			throw new Exception("$extension is not a valid extension");
		}
		Session::setAttribute('currentExtension', $extension);
        // section 127-0-1-1-5f1894ad:12457319d43:-8000:0000000000001A66 end
    }

    /**
     * Short description of method getCurrentExtension
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return string
     */
    public function getCurrentExtension()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-5f1894ad:12457319d43:-8000:0000000000001A6A begin
		if(!Session::hasAttribute('currentExtension')){
			return false;
		}
		$returnValue = Session::getAttribute('currentExtension');
        // section 127-0-1-1-5f1894ad:12457319d43:-8000:0000000000001A6A end

        return (string) $returnValue;
    }

    /**
     * Short description of method loadExtensionStructure
     *
     * @access protected
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  string extension
     * @return SimpleXMLElement
     */
    protected function loadExtensionStructure($extension)
    {
        $returnValue = null;

        // section 127-0-1-1-5f1894ad:12457319d43:-8000:0000000000001A6C begin
		$structureFilePath = ROOT_PATH.'/'.$extension.'/actions/structure.xml';
		if(file_exists($structureFilePath)){
			return new SimpleXMLElement($structureFilePath, null, true);
		}
        // section 127-0-1-1-5f1894ad:12457319d43:-8000:0000000000001A6C end

        return $returnValue;
    }

    /**
     * Short description of method getStructure
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  string extension
     * @param  string section
     * @return array
     */
    public function getStructure($extension = '', $section = '')
    {
        $returnValue = array();

        // section 127-0-1-1-5f1894ad:12457319d43:-8000:0000000000001A79 begin
		
		if( count(self::$structure) == 0 ){
			$structure = array();
			foreach($this->getLoadedExtensions() as $loadedExtension){
				$xmlStructure = $this->loadExtensionStructure($loadedExtension);
				if(!is_null($xmlStructure)){
					self::$structure[(int)$xmlStructure['level']] = array('extension' => $loadedExtension, 'data' => $xmlStructure);
				}
			}
			ksort(self::$structure);
		}
		if(!empty($extension)){
			foreach(self::$structure as $structure){
				if($structure['extension'] == $extension){
					if(!empty($section)){
						$xmlStruct = $structure['data'];
						$nodes = $xmlStruct->xpath("//section[@name='{$section}']");
						return $nodes[0];
					}
					return $structure['data'];
				}
			}
		}
		
		$returnValue = self::$structure;
		
        // section 127-0-1-1-5f1894ad:12457319d43:-8000:0000000000001A79 end

        return (array) $returnValue;
    }

} /* end of class tao_models_classes_TaoService */

?>