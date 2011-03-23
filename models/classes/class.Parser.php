<?php

error_reporting(E_ALL);

/**
 * The Parser enables you to load, parse and validate xml content from an xml
 * Usually used for to load and validate the itemContent  property.
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage models_classes
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1-64df0e4a:12af6a1640c:-8000:00000000000025A2-includes begin
// section 127-0-1-1-64df0e4a:12af6a1640c:-8000:00000000000025A2-includes end

/* user defined constants */
// section 127-0-1-1-64df0e4a:12af6a1640c:-8000:00000000000025A2-constants begin
// section 127-0-1-1-64df0e4a:12af6a1640c:-8000:00000000000025A2-constants end

/**
 * The Parser enables you to load, parse and validate xml content from an xml
 * Usually used for to load and validate the itemContent  property.
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage models_classes
 */
class tao_models_classes_Parser
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute source
     *
     * @access protected
     * @var string
     */
    protected $source = '';

    /**
     * Short description of attribute sourceType
     *
     * @access protected
     * @var int
     */
    protected $sourceType = 0;

    /**
     * Short description of attribute errors
     *
     * @access protected
     * @var array
     */
    protected $errors = array();

    /**
     * Short description of attribute valid
     *
     * @access protected
     * @var boolean
     */
    protected $valid = false;

    /**
     * Short description of attribute fileExtension
     *
     * @access protected
     * @var string
     */
    protected $fileExtension = 'xml';

    /**
     * Short description of attribute SOURCE_FILE
     *
     * @access public
     * @var int
     */
    const SOURCE_FILE = 1;

    /**
     * Short description of attribute SOURCE_URL
     *
     * @access public
     * @var int
     */
    const SOURCE_URL = 2;

    /**
     * Short description of attribute SOURCE_STRING
     *
     * @access public
     * @var int
     */
    const SOURCE_STRING = 3;

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string source
     * @param  array options
     * @return mixed
     */
    public function __construct($source, $options = array())
    {
        // section 127-0-1-1-64df0e4a:12af6a1640c:-8000:00000000000025B8 begin
        
    	if(preg_match("/^<\?xml(.*)?/m", trim($source))){
    		$this->sourceType = self::SOURCE_STRING;
    	}
    	else if(preg_match("/^http/", $source)){
    		$this->sourceType = self::SOURCE_URL;
    	}
    	else if(is_file($source)){
    		$this->sourceType = self::SOURCE_FILE;
    	}
    	else{
    		throw new Exception("Denied content in the source parameter! ".get_class($this)." accepts either XML content, a URL to an XML Content or the path to a file.");
    	}
    	$this->source = $source;
    	
    	if(isset($options['extension'])){
    		$this->fileExtension = $options['extension'];
    	}
    	
        // section 127-0-1-1-64df0e4a:12af6a1640c:-8000:00000000000025B8 end
    }

    /**
     * Short description of method validate
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string schema
     * @return boolean
     */
    public function validate($schema = '')
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-64df0e4a:12af6a1640c:-8000:00000000000025BB begin
        
        $forced = $this->valid;
        
        $this->valid = true;
        
        try{
        	switch($this->sourceType){
        		case self::SOURCE_FILE:
	        		//check file
			   		if(!file_exists($this->source)){
			    		throw new Exception("File {$this->source} not found.");
			    	}
			    	if(!is_readable($this->source)){
			    		throw new Exception("Unable to read file {$this->source}.");
			    	}
			   		if(!preg_match("/\.{$this->fileExtension}$/", basename($this->source))){
			    		throw new Exception("Wrong file extension in ".basename($this->source).", {$this->fileExtension} extension is expected");
			    	}
			   		if(!tao_helpers_File::securityCheck($this->source)){
			    		throw new Exception("{$this->source} seems to contain some security issues");
			    	}
			    	break;
        		case self::SOURCE_URL:
	        		//only same domain
	        		if(!preg_match("/^".preg_quote(BASE_URL, '/')."/", $this->source)){
	        			throw new Exception("The given uri must be in the domain {$_SERVER['HTTP_HOST']}");
	        		}
	        		break;
        	}
        }
        catch(Exception $e){
        	if($forced){
        		throw $e;
        	}
        	else{
        		$this->addError($e);
        	}
        }   
             
        if($this->valid && !$forced){	//valida can be true if forceValidation has been called
        	
        	$this->valid = false;

        	try{
	    		
	    		libxml_use_internal_errors(true);
	    		
		    	$dom = new DomDocument();
		    	$loadResult = false;
		    	switch($this->sourceType){
		    		case self::SOURCE_FILE:
		    			$loadResult = $dom->load($this->source);
		    			break;
		    		case self::SOURCE_URL:
		    			$xmlContent = tao_helpers_Request::load($this->source, true);
		    			$loadResult = $dom->loadXML($xmlContent);
		    			break;
		    		case self::SOURCE_STRING:
		    			$loadResult = $dom->loadXML($this->source);
		    			break;
		    	}
		    	if($loadResult){
		    		if(!empty($schema)){
		    			$this->valid = $dom->schemaValidate($schema);
		    		}
		    		else{
		    			$this->valid = true;	//only well-formed
		    		}
		    	}
		    	
		    	if(!$this->valid){
		    		$this->addErrors(libxml_get_errors());
		    	}
		    	libxml_clear_errors();
	    	}
	    	catch(DOMException $de){
	    		$this->addError($de);
	    	}
        }
    	$returnValue = $this->valid;
        
        // section 127-0-1-1-64df0e4a:12af6a1640c:-8000:00000000000025BB end

        return (bool) $returnValue;
    }

    /**
     * Short description of method isValid
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return boolean
     */
    public function isValid()
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-64df0e4a:12af6a1640c:-8000:00000000000025C1 begin
        
        $returnValue = $this->valid;
        
        // section 127-0-1-1-64df0e4a:12af6a1640c:-8000:00000000000025C1 end

        return (bool) $returnValue;
    }

    /**
     * Short description of method forceValidation
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    public function forceValidation()
    {
        // section 127-0-1-1-64df0e4a:12af6a1640c:-8000:00000000000025C3 begin
        
    	$this->valid = true;
    	
        // section 127-0-1-1-64df0e4a:12af6a1640c:-8000:00000000000025C3 end
    }

    /**
     * Short description of method getErrors
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return array
     */
    public function getErrors()
    {
        $returnValue = array();

        // section 127-0-1-1-64df0e4a:12af6a1640c:-8000:00000000000025C5 begin
        
        $returnValue = $this->errors;
        
        // section 127-0-1-1-64df0e4a:12af6a1640c:-8000:00000000000025C5 end

        return (array) $returnValue;
    }

    /**
     * Short description of method displayErrors
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  boolean htmlOutput
     * @return string
     */
    public function displayErrors($htmlOutput = true)
    {
        $returnValue = (string) '';

        // section 127-0-1-1-64df0e4a:12af6a1640c:-8000:00000000000025C7 begin
        
    	foreach($this->errors as $error){
			$returnValue .= "{$error['message']} in file {$error['file']}, line {$error['line']}\n";
		}
		
		if($htmlOutput){
			$returnValue = nl2br($returnValue);
		}
        
        // section 127-0-1-1-64df0e4a:12af6a1640c:-8000:00000000000025C7 end

        return (string) $returnValue;
    }

    /**
     * Short description of method addError
     *
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  mixed error
     * @return mixed
     */
    protected function addError($error)
    {
        // section 127-0-1-1-64df0e4a:12af6a1640c:-8000:00000000000025CF begin
        
    	$this->valid = false;
    	
    	if($error instanceof Exception){
    		$this->errors[] = array(
    			'file' 		=> $error->getFile(),
    			'line' 		=> $error->getLine(),
    			'message'	=> "[".get_class($error)."] ".$error->getMessage()
    		);
    	}
    	if($error instanceof LibXMLError){
    		$this->errors[] = array(
    			'file' 		=> $error->file,
    			'line'		=> $error->line,
    			'message'	=> "[".get_class($error)."] ".$error->message
    		);
    	}
    	
        // section 127-0-1-1-64df0e4a:12af6a1640c:-8000:00000000000025CF end
    }

    /**
     * Short description of method addErrors
     *
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  array errors
     * @return mixed
     */
    protected function addErrors($errors)
    {
        // section 127-0-1-1-64df0e4a:12af6a1640c:-8000:00000000000025D4 begin
        
   		foreach($errors as $error){
    		$this->addError($error);
    	}
    	
        // section 127-0-1-1-64df0e4a:12af6a1640c:-8000:00000000000025D4 end
    }

    /**
     * Short description of method clearErrors
     *
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    protected function clearErrors()
    {
        // section 127-0-1-1-64df0e4a:12af6a1640c:-8000:00000000000025D2 begin
        
    	$this->errors = array();
    	
        // section 127-0-1-1-64df0e4a:12af6a1640c:-8000:00000000000025D2 end
    }

} /* end of class tao_models_classes_Parser */

?>