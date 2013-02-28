<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/form/elements/xhtml/class.GenerisAsyncFile.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 28.02.2013, 17:19:40 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Jerome Bogaerts, <jerome@taotesting.com>
 * @package tao
 * @subpackage helpers_form_elements_xhtml
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_helpers_form_elements_GenerisAsyncFile
 *
 * @author Jerome Bogaerts, <jerome@taotesting.com>
 */
require_once('tao/helpers/form/elements/class.GenerisAsyncFile.php');

/* user defined includes */
// section 127-0-1-1-37c605c1:13d218622e6:-8000:0000000000003C61-includes begin
// section 127-0-1-1-37c605c1:13d218622e6:-8000:0000000000003C61-includes end

/* user defined constants */
// section 127-0-1-1-37c605c1:13d218622e6:-8000:0000000000003C61-constants begin
// section 127-0-1-1-37c605c1:13d218622e6:-8000:0000000000003C61-constants end

/**
 * Short description of class tao_helpers_form_elements_xhtml_GenerisAsyncFile
 *
 * @access public
 * @author Jerome Bogaerts, <jerome@taotesting.com>
 * @package tao
 * @subpackage helpers_form_elements_xhtml
 */
class tao_helpers_form_elements_xhtml_GenerisAsyncFile
    extends tao_helpers_form_elements_GenerisAsyncFile
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method feed
     *
     * @access public
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     * @return mixed
     */
    public function feed()
    {
        // section 127-0-1-1-37c605c1:13d218622e6:-8000:0000000000003C62 begin
    	if (isset($_POST[$this->name])) {
    		$struct = @unserialize($_POST[$this->name]);
    		if($struct !== false){
    			$desc = new tao_helpers_form_data_UploadFileDescription(	$struct['name'],
    					$struct['size'],
    					$struct['type'],
    					$struct['uploaded_file']);
    			$this->setValue($desc);
    		}
    		else{
    			// else, no file was selected by the end user.
    			// set the value as empty in order
    			$this->setValue($_POST[$this->name]);
    		}
    	}
        // section 127-0-1-1-37c605c1:13d218622e6:-8000:0000000000003C62 end
    }

    /**
     * Short description of method render
     *
     * @access public
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     * @return string
     */
    public function render()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-37c605c1:13d218622e6:-8000:0000000000003C64 begin
        $widgetName = $this->buildWidgetName();
        $widgetContainerId = $this->buildWidgetContainerId();
        
        $returnValue .= "<label class='form_desc' for='{$this->name}'>". _dh($this->getDescription())."</label>";
        $returnValue .= "<div id='${widgetContainerId}' class='form-elt-container file-uploader'>";
        
        if ($this->value instanceof tao_helpers_form_data_UploadFileDescription ||
        		$this->value instanceof tao_helpers_form_data_StoredFileDescription){
        	 
        	// A file is stored or has just been uploaded.
        	$shownFileName = $this->value->getName();
        	$shownFileSize = $this->value->getSize();
        	$deleteButtonTitle = __("Delete");
        	$deleteButtonId = $this->buildDeleteButtonId();
        	$returnValue .= "<span class=\"widget_AsyncFile_fileinfo\">${shownFileName} (${shownFileSize} bytes)</span>";
        	$returnValue .= "<button id=\"${deleteButtonId}\" type=\"button\" title=\"${deleteButtonTitle}\"/>";
        	 
        	// Inject behaviour of the Delete button component in response.
        	$returnValue .= self::embedBehaviour($this->buildDeleterBehaviour());
        }
        else{
        	 
        	// No file stored yet.
        	// Inject behaviour of the AsyncFileUpload component in response.
        	$returnValue .= self::embedBehaviour($this->buildUploaderBehaviour());
        }
        
        
        $returnValue .= "</div>";
        // section 127-0-1-1-37c605c1:13d218622e6:-8000:0000000000003C64 end

        return (string) $returnValue;
    }

    /**
     * Short description of method getEvaluatedValue
     *
     * @access public
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     * @return mixed
     */
    public function getEvaluatedValue()
    {
        // section 127-0-1-1-37c605c1:13d218622e6:-8000:0000000000003C66 begin
    	return $this->getRawValue();
        // section 127-0-1-1-37c605c1:13d218622e6:-8000:0000000000003C66 end
    }

    /**
     * Short description of method buildDeleterBehaviour
     *
     * @access public
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     * @return string
     */
    public function buildDeleterBehaviour()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-37c605c1:13d218622e6:-8000:0000000000003C68 begin
        $deleteButtonId = $this->buildDeleteButtonId();
         
        $returnValue .= '$(document).ready(function() {';
        $returnValue .= '	$("#' . $deleteButtonId . '").click(function() {';
        $returnValue .= '		$("#' . $this->buildWidgetContainerId() . '").empty();';
        $returnValue .= '		' . $this->buildUploaderBehaviour();
        $returnValue .= '	});';
        $returnValue .= '});';
        // section 127-0-1-1-37c605c1:13d218622e6:-8000:0000000000003C68 end

        return (string) $returnValue;
    }

    /**
     * Short description of method buildUploaderBehaviour
     *
     * @access public
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     * @return string
     */
    public function buildUploaderBehaviour()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-37c605c1:13d218622e6:-8000:0000000000003C6A begin
        $widgetName = $this->buildWidgetName();
         
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
        
        //default value for 'auto' is 'true':
        $auto = 'true';
        if(isset($this->attributes['auto'])){
        	if(!$this->attributes['auto'] || $this->attributes['auto'] === 'false') $auto = 'false';
        	unset($this->attributes['auto']);
        }
        
        //initialize the AsyncFileUpload Js component
        $id = md5($this->name);
         
        // Build elements as a string that will be injected by jquery.
        $elements = "<div id=\"{$widgetName}_container\" class=\"form-elt-container file-uploader\">";
        $elements .= "<input type=\"hidden\" name=\"{$this->name}\" id=\"{$this->name}\" value=\"\" />";
        $elements .= "<input type=\"file\" name=\"{$widgetName}\" id=\"{$widgetName}\" ";
        $elements .= $this->renderAttributes();
        $elements .= " value=\" \"/>";
         
        $elements .= "<br/><span>";
        $elements .= "<img src=\"" . TAOBASE_WWW . "img/file_upload.png\" class=\"icon\" />";
        $elements .= "<a href=\"#\" id=\"{$widgetName}_starter\" >" . __('Start upload') . "</a>";
        $elements .= "</span>";
         
        $returnValue  = '$(document).ready(function() {';
        $returnValue .= '	require(["require", "jquery", "AsyncFileUpload"], function(req, $){';
         
        // DOM Update...
        $returnValue .=	'		$("#' . $this->buildWidgetContainerId() . '").html(\'' . $elements . '\');';
         
        // Scripting...
        $returnValue .= '		myUploader_'.$id.' = new AsyncFileUpload("#' . $widgetName . '",';
        $returnValue .= '			{';
        $returnValue .= '				"scriptData": {"session_id": "' . session_id() . '"},';
        $returnValue .= '				"basePath": "' . TAOBASE_WWW . '",';
        $returnValue .= '				"sizeLimit": ' . $fileSize . ',';
        $returnValue .= '				"starter" : "#' . $widgetName . '_starter",';
        $returnValue .= '				"target": "#' . $widgetName . '_container input[id=\''.$this->name.'\']",';
        $returnValue .= '				"submiter": ".form-submiter",';
        $returnValue .= '				"auto": '.$auto.',';
        $returnValue .= '				"folder": "/"';
         
        if (count($extensions) > 0) {
        	$allowedTypes = implode(', ', $extensions);
        	$allowedTypes = __('Allowed files types: ') . $allowedTypes;
        	$allowedExtensions = implode(';', $extensions);
        
        	$returnValue .='		   ,"fileDesc": "'. $allowedTypes . '"';
        	$returnValue .='		   ,"fileExt": "' . $allowedExtensions . '"';
        }
         
        $returnValue .= '			}'; // End of options.
        $returnValue .= '		);'; // End of AsyncFileUpload instantiation.
        $returnValue .= '	});'; // End of require.
        $returnValue .= '});'; // End of $(document).ready()
        // section 127-0-1-1-37c605c1:13d218622e6:-8000:0000000000003C6A end

        return (string) $returnValue;
    }

    /**
     * Short description of method buildWidgetName
     *
     * @access public
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     * @return string
     */
    public function buildWidgetName()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-37c605c1:13d218622e6:-8000:0000000000003C6C begin
        $returnValue = 'AsyncFileUploader_'.md5($this->name);
        // section 127-0-1-1-37c605c1:13d218622e6:-8000:0000000000003C6C end

        return (string) $returnValue;
    }

    /**
     * Short description of method buildDeleteButtonId
     *
     * @access public
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     * @return string
     */
    public function buildDeleteButtonId()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-37c605c1:13d218622e6:-8000:0000000000003C6E begin
        $returnValue = $this->buildWidgetName() . '_deleter';
        // section 127-0-1-1-37c605c1:13d218622e6:-8000:0000000000003C6E end

        return (string) $returnValue;
    }

    /**
     * Short description of method buildWidgetContainerId
     *
     * @access public
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     * @return string
     */
    public function buildWidgetContainerId()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-37c605c1:13d218622e6:-8000:0000000000003C70 begin
        $returnValue = $this->buildWidgetName() . '_container';
        // section 127-0-1-1-37c605c1:13d218622e6:-8000:0000000000003C70 end

        return (string) $returnValue;
    }

    /**
     * Short description of method embedBehaviour
     *
     * @access public
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     * @param  string behaviour
     * @return string
     */
    public function embedBehaviour($behaviour)
    {
        $returnValue = (string) '';

        // section 127-0-1-1-37c605c1:13d218622e6:-8000:0000000000003C72 begin
        $returnValue = '<script type="text/javascript">' . $behaviour . '</script>';
        // section 127-0-1-1-37c605c1:13d218622e6:-8000:0000000000003C72 end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_form_elements_xhtml_GenerisAsyncFile */

?>