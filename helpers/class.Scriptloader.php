<?php

error_reporting(E_ALL);

/**
 * The scriptloader helper enables you to load web resources dynamically. It
 * now CSS and JS resources.
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1-4955a5a0:1242e3739c6:-8000:00000000000019D1-includes begin
// section 127-0-1-1-4955a5a0:1242e3739c6:-8000:00000000000019D1-includes end

/* user defined constants */
// section 127-0-1-1-4955a5a0:1242e3739c6:-8000:00000000000019D1-constants begin
// section 127-0-1-1-4955a5a0:1242e3739c6:-8000:00000000000019D1-constants end

/**
 * The scriptloader helper enables you to load web resources dynamically. It
 * now CSS and JS resources.
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers
 */
class tao_helpers_Scriptloader
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute CSS
     *
     * @access public
     * @var string
     */
    const CSS = 'css';

    /**
     * Short description of attribute JS
     *
     * @access public
     * @var string
     */
    const JS = 'js';

    /**
     * Short description of attribute jsFiles
     *
     * @access private
     * @var array
     */
    private static $jsFiles = array();

    /**
     * Short description of attribute cssFiles
     *
     * @access private
     * @var array
     */
    private static $cssFiles = array();

    // --- OPERATIONS ---

    /**
     * Short description of method contextInit
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string extension
     * @param  string module
     * @param  string action
     * @return mixed
     */
    public static function contextInit($extension, $module, $action)
    {
        // section 127-0-1-1-38d1d39c:12665a8fe44:-8000:0000000000001E2B begin
		
		$basePath = '/'.$extension.'/views/';
		
		//load module scripts
		$jsModuleFile = $basePath.self::JS.'/'.$module.'.'.self::JS;
		$jsModuleDir = $basePath.self::JS.'/'.$module.'/';
		
		$cssModuleFile = $basePath.self::CSS.'/'.$module.'.'.self::CSS;
		$cssModuleDir = $basePath.self::CSS.'/'.$module.'/';
		
		if(file_exists($jsModuleFile)){
			self::addJsFile($jsModuleFile);
		}
		foreach(glog($jsModuleDir.'*.'.self::JS) as $file){
			self::addJsFile($file);
		}
		if(file_exists($cssModuleFile)){
			self::addCssFile($cssModuleFile);
		}
		foreach(glog($cssModuleDir.'*.'.self::CSS) as $file){
			self::addCssFile($file);
		}
		
		//
		//@todo load action scripts
		//
		
        // section 127-0-1-1-38d1d39c:12665a8fe44:-8000:0000000000001E2B end
    }

    /**
     * define the paths to look for the scripts
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  array paths
     * @param  boolean recursive
     * @param  string filter
     * @return mixed
     */
    public static function setPaths($paths, $recursive = false, $filter = '')
    {
        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:00000000000019E6 begin
		foreach($paths as $path){
    		if(!preg_match("/\/$/", $path)){
    			$path .= '/';
    		}
			if(empty($filter) || strtolower($filter) == tao_helpers_Scriptloader::CSS){
				foreach(glob($path . "*." . tao_helpers_Scriptloader::CSS) as $cssFile){
					self::$cssFiles[] = $path . $cssFile;
				}
			}
			if(empty($filter) || strtolower($filter) == tao_helpers_Scriptloader::JS){
				foreach(glob($path . "*." . tao_helpers_Scriptloader::JS) as $jsFile){
					self::$jsFiles[] = $path . $jsFile;
				}
			}
			if($recursive){
				$dirs = array();
				foreach(scandir($path) as $file){
					if(is_dir($path.$file) && $file != '.' && $file != '..'){
						$dirs[] = $path.$file;
					}
				}
				if(count($dirs) > 0){
					self::setPaths($dirs, true, $filter);
				}
			}
    	}
        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:00000000000019E6 end
    }

    /**
     * add a file to load
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string file
     * @param  string type
     * @return mixed
     */
    public static function addFile($file, $type = '')
    {
        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:00000000000019F4 begin
		if(empty($type)){
			if(preg_match("/\.".tao_helpers_Scriptloader::CSS."$/", $file)){
				$type = tao_helpers_Scriptloader::CSS;
			}
			if(preg_match("/\.".tao_helpers_Scriptloader::JS."$/", $file)){
				$type = tao_helpers_Scriptloader::JS;
			}
		}
		switch(strtolower($type)){
			case tao_helpers_Scriptloader::CSS: self::$cssFiles[] = $file; break;
			case tao_helpers_Scriptloader::JS:  self::$jsFiles[]  = $file; break;
			default:
				throw new Exception("Unknown script type for file : ".$file);
		} 
        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:00000000000019F4 end
    }

    /**
     * add a css file to load
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string file
     * @return mixed
     */
    public static function addCssFile($file)
    {
        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:00000000000019FB begin
		self::addFile($file, tao_helpers_Scriptloader::CSS);
        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:00000000000019FB end
    }

    /**
     * add a js file to load
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string file
     * @return mixed
     */
    public static function addJsFile($file)
    {
        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:00000000000019FF begin
		self::addFile($file, tao_helpers_Scriptloader::JS);
        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:00000000000019FF end
    }

    /**
     * add an array of css files to load
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  array files
     * @return mixed
     */
    public static function addCssFiles($files = array())
    {
        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:0000000000001A02 begin
		foreach($files as $file){
			self::addFile($file, tao_helpers_Scriptloader::CSS);
		}
        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:0000000000001A02 end
    }

    /**
     * add an array of css files to load
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  array files
     * @return mixed
     */
    public static function addJsFiles($files = array())
    {
        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:0000000000001A0C begin
		foreach($files as $file){
			self::addFile($file, tao_helpers_Scriptloader::JS);
		}
        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:0000000000001A0C end
    }

    /**
     * render the html to load the resources
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string filter
     * @return string
     */
    public static function render($filter = '')
    {
        $returnValue = (string) '';

        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:0000000000001A17 begin
		if(empty($filter) || strtolower($filter) == tao_helpers_Scriptloader::CSS){
			foreach(self::$cssFiles as $file){
				$returnValue .= "\t<link rel='stylesheet' type='text/css' href='{$file}' />\n";
			}
		}
		if(empty($filter) || strtolower($filter) == tao_helpers_Scriptloader::JS){
			foreach(self::$jsFiles as $file){
				$returnValue .= "\t<script type='text/javascript' src='{$file}' ></script>\n";
			}
		}
        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:0000000000001A17 end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_Scriptloader */

?>