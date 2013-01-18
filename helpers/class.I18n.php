<?php

error_reporting(E_ALL);

/**
 * Internationalization helper.
 *
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 * @subpackage helpers
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1--7d879eb4:12693e522d7:-8000:0000000000001E7B-includes begin
// section 127-0-1-1--7d879eb4:12693e522d7:-8000:0000000000001E7B-includes end

/* user defined constants */
// section 127-0-1-1--7d879eb4:12693e522d7:-8000:0000000000001E7B-constants begin
// section 127-0-1-1--7d879eb4:12693e522d7:-8000:0000000000001E7B-constants end

/**
 * Internationalization helper.
 *
 * @access public
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 * @subpackage helpers
 */
class tao_helpers_I18n
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute AVAILABLE_LANGS_CACHEKEY
     *
     * @access private
     * @var string
     */
    const AVAILABLE_LANGS_CACHEKEY = 'i18n_available_langs';

    /**
     * Short description of attribute langCode
     *
     * @access private
     * @var string
     */
    private static $langCode = '';

    /**
     * Short description of attribute availableLangs
     *
     * @access protected
     * @var array
     */
    protected static $availableLangs = array();

    // --- OPERATIONS ---

    /**
     * Short description of method init
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string langCode
     * @return mixed
     */
    public static function init($langCode)
    {
        // section 127-0-1-1--7d879eb4:12693e522d7:-8000:0000000000001E7C begin
		   	
    	// if the langCode is empty do nothing
    	if (empty($langCode)){
    		throw new Exception("Language is not defined");
    	}
    	
		self::$langCode = $langCode;
		
		
		//only for backward compatibility
		$_SESSION['lang'] = self::$langCode;
		$_SESSION['ui_lang'] = self::$langCode;

		//init the ClearFw l10n tools
		l10n::init();
		l10n::set(BASE_PATH.'locales/'.self::$langCode.'/messages');
		
        if (PHP_SAPI != 'cli') {
            $mvcContext = Context::getInstance();
            $mvcRequest = $mvcContext->getRequest();
            if ($mvcRequest->hasParameter('extension') &&
                ($ext = $mvcRequest->getParameter('extension')) != 'none' &&
                $ext != 'users'){
                $jsI18nPath = ROOT_URL . '/' . $ext;
            }
            else{
                $jsI18nPath = BASE_URL;
            }
            
    		tao_helpers_Scriptloader::addJsFiles(array(
    			$jsI18nPath . '/locales/'.self::$langCode.'/messages_po.js',
    			TAOBASE_WWW . 'js/i18n.js',
    		));
        }
        // section 127-0-1-1--7d879eb4:12693e522d7:-8000:0000000000001E7C end
    }

    /**
     * Short description of method getLangCode
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return string
     */
    public static function getLangCode()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-12d76932:128aaed4c91:-8000:0000000000001FAA begin
        
        $returnValue = self::$langCode;
        
        // section 127-0-1-1-12d76932:128aaed4c91:-8000:0000000000001FAA end

        return (string) $returnValue;
    }

    /**
     * Short description of method getLangResourceByCode
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string code
     * @return core_kernel_classes_Resource
     */
    public static function getLangResourceByCode($code)
    {
        $returnValue = null;

        // section 127-0-1-1-3cbd0a97:12a8039803a:-8000:0000000000002491 begin
        
        if(!empty($code)){
	        $langClass = new core_kernel_classes_Class(CLASS_LANGUAGES);
	        $valueProperty = new core_kernel_classes_Property(RDF_VALUE);
                foreach($langClass->getInstances() as $lang){
                        $lgPropertyValue = $lang->getUniquePropertyValue($valueProperty);
                        if(trim($lgPropertyValue) == trim($code)){
                                $returnValue = $lang;
                                break;
                        }
                }
        }
        
        // section 127-0-1-1-3cbd0a97:12a8039803a:-8000:0000000000002491 end

        return $returnValue;
    }

    /**
     * This method returns the languages available in TAO.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  boolean langName If set to true, an associative array where keys are language codes and values are language labels. If set to false (default), a simple array of language codes is returned.
     * @return array
     */
    public static function getAvailableLangs($langName = false)
    {
        $returnValue = array();

        // section 127-0-1-1-3cbd0a97:12a8039803a:-8000:0000000000002494 begin
        
        //get it into the api only once 
        if(count(self::$availableLangs) == 0){
        	try {
        		self::$availableLangs = common_cache_FileCache::singleton()->get(self::AVAILABLE_LANGS_CACHEKEY);
        	} catch (common_cache_NotFoundException $e) {
	        	$langClass = new core_kernel_classes_Class(CLASS_LANGUAGES);
	        	$valueProperty = new core_kernel_classes_Property(RDF_VALUE);
	        	foreach($langClass->getInstances() as $lang){
	               	self::$availableLangs[] = $lang->getUniquePropertyValue($valueProperty)->literal;
	        	}
	        	
	        	common_cache_FileCache::singleton()->put(self::$availableLangs, self::AVAILABLE_LANGS_CACHEKEY);
        	}
        }
	
        if($langName) {
        	foreach(self::$availableLangs as $code){
        		$lang = self::getLangResourceByCode($code);
       			$returnValue[$code] = $lang->getLabel(); 
        	}
        }
        else{
       		$returnValue = self::$availableLangs;
        }
        
        // section 127-0-1-1-3cbd0a97:12a8039803a:-8000:0000000000002494 end

        return (array) $returnValue;
    }

    /**
     * Get available languages from the knownledge base depending on a specific
     * usage (GUI, Data, ...)
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  Resource usage An instance of tao:LanguagesUsages from the knowledge base.
     * @return array
     */
    public static function getAvailableLangsByUsage( core_kernel_classes_Resource $usage)
    {
        $returnValue = array();

        // section 10-13-1-85-4d042f2c:13ada6173fa:-8000:0000000000003C1B begin
    	$langClass = new core_kernel_classes_Class(CLASS_LANGUAGES);
	    $valueProperty = new core_kernel_classes_Property(RDF_VALUE);
	    $usageProperty = new core_kernel_classes_Property(PROPERTY_LANGUAGE_USAGES);
	    foreach($langClass->getInstances() as $lang){
	    	$code = $lang->getUniquePropertyValue($valueProperty)->literal;
	    	$lang = self::getLangResourceByCode($code);
	    	$usages = $lang->getPropertyValues($usageProperty);
	    	
	    	foreach ($usages as $u){
	    		if ($u == $usage->getUri()){
	    			$returnValue[$code] = $lang->getLabel();
	    			break;
	    		}
	    	}
	    }
        // section 10-13-1-85-4d042f2c:13ada6173fa:-8000:0000000000003C1B end

        return (array) $returnValue;
    }

} /* end of class tao_helpers_I18n */

?>