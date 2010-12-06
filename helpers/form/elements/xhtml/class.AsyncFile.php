<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API -
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 28.04.2010, 10:27:04 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form_elements_xhtml
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_helpers_form_elements_AsyncFile
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
require_once('tao/helpers/form/elements/class.AsyncFile.php');

/* user defined includes */
// section 127-0-1-1--79a39ca7:12824fe53d5:-8000:00000000000023D9-includes begin
// section 127-0-1-1--79a39ca7:12824fe53d5:-8000:00000000000023D9-includes end

/* user defined constants */
// section 127-0-1-1--79a39ca7:12824fe53d5:-8000:00000000000023D9-constants begin
// section 127-0-1-1--79a39ca7:12824fe53d5:-8000:00000000000023D9-constants end

/**
 * Short description of class tao_helpers_form_elements_xhtml_AsyncFile
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form_elements_xhtml
 */
class tao_helpers_form_elements_xhtml_AsyncFile
    extends tao_helpers_form_elements_AsyncFile
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method getValue
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    public function getValue()
    {
        $returnValue = null;

        // section 127-0-1-1-3ba812e2:1284379704f:-8000:00000000000023F8 begin
        
        if(!is_null($this->value)){
        	$struct = @unserialize($this->value);
        	if($struct !== false){
        		$this->value = $struct;
        	}
        }
        $returnValue = $this->value;
        
        // section 127-0-1-1-3ba812e2:1284379704f:-8000:00000000000023F8 end

        return $returnValue;
    }

    /**
     * Short description of method render
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return string
     */
    public function render()
    {
        $returnValue = (string) '';

        // section 127-0-1-1--79a39ca7:12824fe53d5:-8000:00000000000023DE begin
        
        $widgetName = $this->name.'-AsyncFileUploader';
        
        $returnValue .= "<label class='form_desc' for='{$this->name}'>".$this->getDescription()."</label>";
		
        $returnValue .= "<div class='form-elt-container file-uploader'>";
        $returnValue .= "<input type='hidden' name='{$this->name}' id='{$this->name}' value='' />";
        $returnValue .= "<input type='file' name='{$widgetName}' id='{$widgetName}' ";
		$returnValue .= $this->renderAttributes();
		$returnValue .= " value='{$this->value}'  />";
		
		$returnValue .= "<br /><span>";
		$returnValue .= "<img src='".TAOBASE_WWW."img/file_upload.png' class='icon' />";
		$returnValue .= "<a href='#' id='{$widgetName}_starter' >".__('Start upload')."</a>";
		$returnValue .= "</span>";

		//get the upload max size (the min of those 3 directives)
		$max_upload = (int)(ini_get('upload_max_filesize'));
		$max_post = (int)(ini_get('post_max_size'));
		$memory_limit = (int)(ini_get('memory_limit'));
		$fileSize = min($max_upload, $max_post, $memory_limit) * 1024 * 1024;
		
		$extensions = array();
		
		//add a client validation
		foreach($this->validators as $validator){
			//get the valid file extensions
			if($validator instanceof tao_helpers_form_validators_FileMimeType){
				$options = $validator->getOptions();
				if(isset($options['extension'])){
					foreach($options['extension'] as $extension){
						$extensions[] = '*.'.$extension;
					}
				}
			}
			//get the max file size
			if($validator instanceof tao_helpers_form_validators_FileSize){
				$options = $validator->getOptions();
				if(isset($options['max'])){
					$validatorMax = (int)$options['max'];
					if($validatorMax > 0 && $validatorMax < $fileSize){
						$fileSize = $validatorMax;
					}
				}
			}
		}
		
		//initialize the AsyncFileUpload Js component
		$returnValue .= '<script type="text/javascript">
			$(document).ready(function(){
				
				new AsyncFileUpload("#'.$widgetName.'", {
					"scriptData"	: {"session_id": "'.session_id().'"},
					"basePath"  : "'.TAOBASE_WWW.'",
					"sizeLimit"	: '.$fileSize.',';
		if(count($extensions) > 0){
 			$returnValue .='
					"fileDesc"	: "'.__('Allowed files types: ').implode(', ', $extensions).'",
					"fileExt"	: "'.implode(';', $extensions).'",';
		}
		$returnValue .='
					"starter"   : "#'.$widgetName.'_starter",
					"target"	: "#'.$this->name.'",
					"submiter"	: ".form-submiter",
					"folder"    : "/"
				});
				
			});
			</script>';
        $returnValue .= "</div>";
		
        // section 127-0-1-1--79a39ca7:12824fe53d5:-8000:00000000000023DE end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_form_elements_xhtml_AsyncFile */

?>