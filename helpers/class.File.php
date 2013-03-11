<?php

error_reporting(E_ALL);

/**
 * Utilities on files
 *
 * @access public
 * @author Lionel Lecaque, <lionel@taotesting.com>
 * @package tao
 * @subpackage helpers
 */
class tao_helpers_File
    extends helpers_File
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
     * @author Lionel Lecaque, <lionel@taotesting.com>
     * @param  string path
     * @param  boolean traversalSafe
     * @return boolean
     */
    public static function securityCheck($path, $traversalSafe = false)
    {
        $returnValue = (bool) false;



   		$returnValue = true;

        //security check: detect directory traversal (deny the ../)
		if($traversalSafe){
	   		if(preg_match("/\.\.\//", $path)){
				$returnValue = false;
				common_Logger::w('directory traversal detected in ' . $path);
			}
		}

		//security check:  detect the null byte poison by finding the null char injection
		if($returnValue){
			for($i = 0; $i < strlen($path); $i++){
				if(ord($path[$i]) === 0){
					$returnValue = false;
					common_Logger::w('null char injection detected in ' . $path);
					break;
				}
			}
		}

        return (bool) $returnValue;
    }

    /**
     * clean concat paths
     *
     * @access public
     * @author Lionel Lecaque, <lionel@taotesting.com>
     * @param  array paths
     * @return string
     */
    public static function concat($paths)
    {
        $returnValue = (string) '';

        foreach($paths as $path){
        	if(!preg_match("/\/$/", $returnValue) && !preg_match("/^\//", $path) && !empty($returnValue)){
        		$returnValue .= '/';
        	}
        	$returnValue .= $path;
        }
        $returnValue = str_replace('//', '/', $returnValue);

        return (string) $returnValue;
    }

    /**
     * Remove file, may be recursively
     *
     * @access public
     * @author Lionel Lecaque, <lionel@taotesting.com>
     * @param  string path
     * @param  boolean recursive
     * @return boolean
     */
    public static function remove($path, $recursive = false)
    {
        $returnValue = (bool) false;


		if ($recursive) {
			$returnValue = helpers_File::remove($path);
		} elseif (is_file($path)) {
        	$returnValue = @unlink($path);
        }
        // else fail silently

        return (bool) $returnValue;
    }

    /**
     * Move file from source to destination
     *
     * @access public
     * @author Lionel Lecaque, <lionel@taotesting.com>
     * @param  string source
     * @param  string destination
     * @return boolean
     */
    public static function move($source, $destination)
    {
        $returnValue = (bool) false;

        if(is_dir($source)){
			if(!file_exists($destination)){
				mkdir($destination, 0777, true);
			}
			$error = false;
			foreach(scandir($source) as $file){
				if($file != '.' && $file != '..'){
					if(is_dir($source.'/'.$file)){
						if(!self::move($source.'/'.$file, $destination.'/'.$file, true)){
							$error = true;
						}
					}
					else{
						if(!self::copy($source.'/'.$file, $destination.'/'.$file, true)){
							$error = true;
						}
					}
				}
			}
			if(!$error){
				$returnValue = true;
			}
			self::remove($source, true);
		}
		else{
	        if(file_exists($source) && file_exists($destination)){
	        	$returnValue = rename($source, $destination);
	        }
	        else{
	        	if(self::copy($source, $destination, true)){
	        		$returnValue = self::remove($source);
	        	}
	        }
		}

        return (bool) $returnValue;
    }

    /**
     * Retrieve accepted Mime Types
     *
     * @access protected
     * @author Lionel Lecaque, <lionel@taotesting.com>
     * @return array
     */
    protected static function getMimeTypes()
    {
        $returnValue = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'text/xml',
            'rdf' => 'text/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',
            'csv' => 'text/csv',
            'rtx' => 'text/richtext',
            'rtf' => 'text/rtf',

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
            'ogv' => 'video/ogg',
            'oga' => 'audio/ogg',

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

        return (array) $returnValue;
    }

    /**
     * Retrieve file extensions for a given Mime Type
     *
     * @access public
     * @author Lionel Lecaque, <lionel@taotesting.com>
     * @param  string mimeType
     * @return string
     */
    public static function getExtention($mimeType)
    {
        $returnValue = (string) '';

        $mime_types = self::getMimeTypes();

        foreach($mime_types as $key => $value){
        	if($value == trim($mimeType)){
        		$returnValue = $key;
        		break;
        	}
        }

        return (string) $returnValue;
    }

    /**
     * get the mime-type of the file in parameter.
     * different methods are used regarding the configuration.
     *
     * @access public
     * @author Lionel Lecaque, <lionel@taotesting.com>
     * @param  string path
     * @param  boolean ext If set to true, the extension of the file will be used to retrieve the mime-type. If now extension can be found, 'text/plain' is returned by the method.
     * @return string
     */
    public static function getMimeType($path, $ext = false)
    {
        $mime_types = self::getMimeTypes();
        
        if (false == $ext){
        	$ext = pathinfo($path, PATHINFO_EXTENSION);
        	
        	if (array_key_exists($ext, $mime_types)) {
        		$mimetype =  $mime_types[$ext];
        	} 
        	else {
        		$mimetype = '';
        	}
        	
        	if (!in_array($ext, array('css'))) {
        		if  (file_exists($path)) {
        			if (function_exists('finfo_open')) {
        				$finfo = finfo_open(FILEINFO_MIME);
        				$mimetype = finfo_file($finfo, $path);
        				finfo_close($finfo);
        			}
        			else if (function_exists('mime_content_type')) {
        				$mimetype = mime_content_type($path);
        			}
        			if (!empty($mimetype)) {
        				if (preg_match("/; charset/", $mimetype)) {
        					$mimetypeInfos = explode(';', $mimetype);
        					$mimetype = $mimetypeInfos[0];
        				}
        			}
        		}
        	}
        }
        else{
        	// find out the mime-type from the extension of the file.
        	$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        	if (array_key_exists($ext, $mime_types)){
        		$mimetype = $mime_types[$ext];
        	}
        }
		
        // If no mime-type found ...
        if (empty($mimetype)) {
        	$mimetype =  'application/octet-stream';
        }

		return (string) $mimetype;
    }

    /**
     * creates a directory in the systems tempdir
     *
     * @access public
     * @author Lionel Lecaque, <lionel@taotesting.com>
     * @return string
     */
    public static function createTempDir()
    {

        do {
			$folder = sys_get_temp_dir().DIRECTORY_SEPARATOR."tmp".mt_rand();
		} while(file_exists($folder));
		mkdir($folder);
		return $folder;
    }

    /**
     * deletes a directory and its content
     *
     * @access public
     * @author Lionel Lecaque, <lionel@taotesting.com>
     * @param  string directory absolute path of the directory
     * @return boolean
     */
    public static function delTree($directory)
    {

        $files = array_diff(scandir($directory), array('.','..'));
		foreach ($files as $file) {
			$abspath = $directory.DIRECTORY_SEPARATOR.$file;
			if (is_dir($abspath)) {
				self::delTree($abspath);
			} else {
				unlink($abspath);
			}
		}
		return rmdir($directory);

    }

} /* end of class tao_helpers_File */

?>