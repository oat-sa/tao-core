<?php

error_reporting(E_ALL);

/**
 * This class provide the services for the Tao extension
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
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
 * @author Joel Bout, <joel.bout@tudor.lu>
 */
require_once('tao/models/classes/class.GenerisService.php');

/* user defined includes */
// section 10-13-1-45-792423e0:12398d13f24:-8000:0000000000001822-includes begin
// section 10-13-1-45-792423e0:12398d13f24:-8000:0000000000001822-includes end

/* user defined constants */
// section 10-13-1-45-792423e0:12398d13f24:-8000:0000000000001822-constants begin
// section 10-13-1-45-792423e0:12398d13f24:-8000:0000000000001822-constants end

/**
 * This class provide the services for the Tao extension
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage models_classes
 */
class tao_models_classes_TaoService
    extends tao_models_classes_GenerisService
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * to stock the list of extensions
     *
     * @access private
     * @var array
     */
    private static $extensions = array();

    /**
     * to stock the extension structure
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
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return array
     */
    public function getLoadedExtensions()
    {
        $returnValue = array();

        // section 10-13-1-45-792423e0:12398d13f24:-8000:0000000000001820 begin

		if( count(self::$extensions) == 0 ){		//check it only once

			$extensionsManager = common_ext_ExtensionsManager::singleton();
			foreach($extensionsManager->getInstalledExtensions() as $extension){
				if($extension->isEnabled()){
					self::$extensions[] = $extension->getID();
				}
			}
		}

		$returnValue = self::$extensions;

        // section 10-13-1-45-792423e0:12398d13f24:-8000:0000000000001820 end

        return (array) $returnValue;
    }

    /**
     * Check if an extension is loaded
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
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
     * define the current extension
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  string extension
     * @return mixed
     */
    public function setCurrentExtension($extension)
    {
        // section 127-0-1-1-5f1894ad:12457319d43:-8000:0000000000001A66 begin
    	common_Logger::w('Deprecated function setCurrentExtension(TaoService) called');
    	$returnValue = context::getInstance()->setExtensionName($extension);
        // section 127-0-1-1-5f1894ad:12457319d43:-8000:0000000000001A66 end
    }

    /**
     * get the current extension
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return string
     */
    public function getCurrentExtension()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-5f1894ad:12457319d43:-8000:0000000000001A6A begin
    	common_Logger::w('Deprecated function getCurrentExtension(TaoService) called');
        $returnValue = context::getInstance()->getExtensionName();
        // section 127-0-1-1-5f1894ad:12457319d43:-8000:0000000000001A6A end

        return (string) $returnValue;
    }

    /**
     * Load the extension structure file.
     * Return the SimpleXmlElement object (don't forget to cast it)
     *
     * @access protected
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  string extension
     * @return SimpleXMLElement
     */
    protected function loadExtensionStructures($extension)
    {
        $returnValue = null;

        // section 127-0-1-1-5f1894ad:12457319d43:-8000:0000000000001A6C begin
		/*
		if($extension == 'users'){
			$structureFilePath = ROOT_PATH.'/tao/actions/users-structure.xml';
		}
		else{
			$structureFilePath = ROOT_PATH.'/'.$extension.'/actions/structure.xml';
		}
		*/
		$structureFilePath = ROOT_PATH.'/'.$extension.'/actions/structures.xml';
		
		if(file_exists($structureFilePath)){
			return new SimpleXMLElement($structureFilePath, null, true);
		}
        // section 127-0-1-1-5f1894ad:12457319d43:-8000:0000000000001A6C end

        return $returnValue;
    }

    /**
     * Short description of method getAllStructures
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return array
     */
    public function getAllStructures()
    {
        $returnValue = array();

        // section 127-0-1-1-64be1e2f:13774f13776:-8000:0000000000003A89 begin
    	if( count(self::$structure) == 0 ){
			$structure = array();
			foreach($this->getLoadedExtensions() as $loadedExtension){
				$xmlStructures = $this->loadExtensionStructures($loadedExtension);
				if(!is_null($xmlStructures)){
					$structures = $xmlStructures->xpath("/structures/structure");
					foreach($structures as $xmlStructure){
						self::$structure[(int)$xmlStructure['level']] = array(
							'extension' => $loadedExtension,
							'id'		=> (string)$xmlStructure['id'],
							'data'		=> $xmlStructure,
							'level'		=> (int)$xmlStructure['level']
						);
					}
				}
			}
			ksort(self::$structure);
		}
		$returnValue = self::$structure;
        // section 127-0-1-1-64be1e2f:13774f13776:-8000:0000000000003A89 end

        return (array) $returnValue;
    }

    /**
     * Get the structure for the extension/section in parameters
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  string extension
     * @param  string structure
     * @return array
     */
    public function getStructure($extension, $structure)
    {
        $returnValue = array();

        // section 127-0-1-1-5f1894ad:12457319d43:-8000:0000000000001A79 begin
		foreach($this->getAllStructures() as $struct){
			if($struct['extension'] == $extension && $struct['id'] == $structure){
				$returnValue = $struct;
				break;
			}
		}
		if (empty($returnValue)) {
			common_logger::w('Structure '.$structure.' not found for extension '.$extension);
    	}

        // section 127-0-1-1-5f1894ad:12457319d43:-8000:0000000000001A79 end

        return (array) $returnValue;
    }

    /**
     * Short description of method getSection
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  string extension
     * @param  string structure
     * @param  string section
     * @return array
     */
    public function getSection($extension, $structure, $section)
    {
        $returnValue = array();

        // section 127-0-1-1-64be1e2f:13774f13776:-8000:0000000000003A84 begin
        $structureArr = $this->getStructure($extension, $structure);
        if(!is_null($structureArr) && isset($structureArr['data'])) {
			$xmlStruct = $structureArr['data'];
			$nodes = $xmlStruct->xpath("//section[@name='{$section}']");
			if(isset($nodes[0])){
				$returnValue = $nodes[0];
			}
		}
		if (empty($returnValue)) {
			common_logger::w('Section '.$section.' not found found for structure '.$structure);
    	}
        // section 127-0-1-1-64be1e2f:13774f13776:-8000:0000000000003A84 end

        return (array) $returnValue;
    }

    /**
     * Check if an extension is an extension loaded inside the TAO GUI
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  string extension
     * @return boolean
     */
    public function isTaoChildExtension($extension)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-34cc9151:127a8ee40c4:-8000:000000000000233E begin

        if(!empty($extension)){
        	if(file_exists(ROOT_PATH.'/'.$extension.'/actions/structure.xml')){
        		 $returnValue = true;
        	}
        }

        // section 127-0-1-1-34cc9151:127a8ee40c4:-8000:000000000000233E end

        return (bool) $returnValue;
    }

} /* end of class tao_models_classes_TaoService */

?>