<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API -
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 22.04.2010, 12:22:57 with ArgoUML PHP module 
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
        
        $returnValue .= "<label class='form_desc' for='{$this->name}'>".$this->getDescription()."</label>";
		
        $returnValue .= "<div class='form-elt-container file-uploader'>";
        $returnValue .= "<input type='file' name='{$this->name}' id='{$this->name}' ";
		$returnValue .= $this->renderAttributes();
		$returnValue .= " value='{$this->value}'  />";
		
		$returnValue .= "<br />";
		$returnValue .= "<img src='".TAOBASE_WWW."img/file_upload.png' class='icon' />";
		$returnValue .= "<a href='#' id='{$this->name}_starter' >".__('Upload files')."</a>";

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
		
		$returnValue .= '<script type="text/javascript">
			$(document).ready(function(){
				$(\'#'.$this->name.'\').uploadify({
					"uploader"  : "'.TAOBASE_WWW.'js/jquery.uploadify-v2.1.0/uploadify.swf",
					"script"    : "'.TAOBASE_WWW.'js/jquery.uploadify-v2.1.0/uploadify.php",
					"cancelImg" : "'.TAOBASE_WWW.'img/cancel.png",
					"buttonImg"	: "'.TAOBASE_WWW.'img/browse_btn.png",
					"width"		: 140,
					"height"	: 40,
					"auto"      : false,
					"sizeLimit"	: '.$fileSize.',
					"onError"	: function(event, queueID, fileObj, errorObj){
						console.log(errorObj);
						console.log(fileObj);
					},
					"onComplete"	: function(event, queueID, fileObj, response, data){
						console.log(fileObj);
						console.log(response);
						console.log(data);
						return false;
					},';
		
		if(count($extensions) > 0){
 			$returnValue .='
					"fileDesc"	: "'.__('Allowed files types: ').implode(', ', $extensions).'",
					"fileExt"	: "'.implode(';', $extensions).'",';
		}
		$returnValue .='
					"buttonText": "'.__('Browse').'",
					"folder"    : "/"
				 });
				 
				 $("#'.$this->name.'_starter").click(function(){
				 	$(\'#'.$this->name.'\').uploadifyUpload();
				 });
			});
			</script>';
        $returnValue .= "</div>";
		
		// section 127-0-1-1--79a39ca7:12824fe53d5:-8000:00000000000023DE end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_form_elements_xhtml_AsyncFile */

?>