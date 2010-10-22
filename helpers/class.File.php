<?php

error_reporting(E_ALL);

/**
 * Utilities on files
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1--8409764:1283ed2f327:-8000:00000000000023EE-includes begin
// section 127-0-1-1--8409764:1283ed2f327:-8000:00000000000023EE-includes end

/* user defined constants */
// section 127-0-1-1--8409764:1283ed2f327:-8000:00000000000023EE-constants begin
// section 127-0-1-1--8409764:1283ed2f327:-8000:00000000000023EE-constants end

/**
 * Utilities on files
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers
 */
class tao_helpers_File
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Check if the path in parameter can be securly used into the application.
     * (check the cross directory injection, the null byte injection, etc.)
     * Use it when the path may be build from a user variable
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string path
     * @return boolean
     */
    public static function securityCheck($path)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--8409764:1283ed2f327:-8000:00000000000023EF begin
        
   		$returnValue = true;
        
        //security check: detect directory traversal (deny the ../)
		/*if(preg_match("/\.\.\//", $path)){
			$returnValue = false;
		}*/
		
		//security check:  detect the null byte poison by finding the null char injection
		for($i = 0; $i < strlen($path); $i++){
			if(ord($path[$i]) === 0){
				$returnValue = false;
			}
		}
        
        // section 127-0-1-1--8409764:1283ed2f327:-8000:00000000000023EF end

        return (bool) $returnValue;
    }

    /**
     * clean concat paths
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  array paths
     * @return string
     */
    public static function concat($paths)
    {
        $returnValue = (string) '';

        // section 127-0-1-1--8409764:1283ed2f327:-8000:00000000000023F2 begin
        
        foreach($paths as $path){
        	if(!preg_match("/\/$/", $returnValue) && !preg_match("/^\//", $path) && !empty($returnValue)){
        		$returnValue .= '/';
        	}
        	$returnValue .= $path;
        }
        $returnValue = str_replace('//','/', $returnValue);
        
        // section 127-0-1-1--8409764:1283ed2f327:-8000:00000000000023F2 end

        return (string) $returnValue;
    }

    /**
     * get the mime-type of the file in parameter.
     * different methods are used regarding the configuration.
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string path
     * @return string
     */
    public static function getMimeType($path)
    {
        $returnValue = (string) '';

        // section 127-0-1-1--5cd35ad1:1283edec322:-8000:00000000000023F5 begin
        
    	$mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',
    		'csv' => 'text/csv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.',$path)));
        
		if (function_exists('mime_content_type')) {
			$mime_type = mime_content_type($path);
		}
		if (function_exists('finfo_open') && empty($mimetype)) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $path);
            finfo_close($finfo);
        }
		if (array_key_exists($ext, $mime_types) && empty($$mimetype)) {
            $mimetype =  $mime_types[$ext];
        }

        if(empty($mimetype)){
            $mimetype =  'application/octet-stream';
        }
        
        error_log($mimetype);
        
        $returnValue =  $mimetype;
        
        // section 127-0-1-1--5cd35ad1:1283edec322:-8000:00000000000023F5 end

        return (string) $returnValue;
    }

    /**
     * Short description of method remove
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string path
     * @param  boolean recursive
     * @return boolean
     */
    public static function remove($path, $recursive = false)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--12179ab4:12bca1f1def:-8000:00000000000026E9 begin
        
        if(is_file($path)){
        	$returnValue = @unlink($path);
        }
        else if($recursive){
        	if(is_dir($path)){
        		$iterator = new DirectoryIterator($path);
				foreach ($iterator as $fileinfo) {
				    if (!$fileinfo->isDot()) {
				        self::remove($fileinfo->getPathname(), true);
				    }
				}
				$returnValue = @rmdir($path);
        	}
        }
        
        // section 127-0-1-1--12179ab4:12bca1f1def:-8000:00000000000026E9 end

        return (bool) $returnValue;
    }

    /**
     * Short description of method copy
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string source
     * @param  string destination
     * @param  boolean recursive
     * @return boolean
     */
    public static function copy($source, $destination, $recursive = true)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--635f654c:12bca305ad9:-8000:00000000000026F3 begin
        
        if(file_exists($source)){
        	if(is_dir(dirname($destination))){
        		$returnValue = copy($source, $destination);
        	}
        	else if($recursive){
        		if(mkdir(dirname($destination), 0775, true)){
        			$returnValue = self::copy($source, $destination, false);
        		}
        	}
        }
        
        // section 127-0-1-1--635f654c:12bca305ad9:-8000:00000000000026F3 end

        return (bool) $returnValue;
    }

    /**
     * Short description of method move
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string source
     * @param  string destination
     * @return boolean
     */
    public static function move($source, $destination)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--44542511:12bd37d6416:-8000:0000000000002718 begin
        
        if(file_exists($source) && file_exists($destination)){
        	$returnValue = rename($source, $destination);
        }
        else{
        	$returnValue = self::copy($source, $destination, true);
        }
        if($returnValue){
        	$returnValue = ($returnValue && self::remove($source));
        }
        
        // section 127-0-1-1--44542511:12bd37d6416:-8000:0000000000002718 end

        return (bool) $returnValue;
    }

} /* end of class tao_helpers_File */

?>