<?php
/**
 * This class aims at providing utilities about the current installation from
 * the host system and its filesystem, including the tao platform directory.
 * 
 * @author Somsack Sipasseuth <somsack.sipasseuth@tudor.lu>
 * @author Jerome Bogaerts <jerome.bogaerts@tudor.lu>
 */
class tao_install_utils_System{
	
	/**
	 * Get informations on the host system.
     * 
	 * @return array where key/values are 'folder' as string, 'host' as string, 'https' as boolean.
	 */
	public static function getInfos(){
                
                
		//subfolder shall be detected as /SUBFLODERS/tao/install/index.php so we remove the "/extension/module/action" part:
                $subfolder = $_SERVER['REQUEST_URI'];
                $subfolder = preg_replace('/\/(([^\/]*)\/){2}([^\/]*)$/', '', $subfolder);
                $subfolder = preg_replace('/^\//', '', $subfolder);
                
                return array(
			'folder'	=> $subfolder,
			'host'		=> $_SERVER['HTTP_HOST'],
			'https'		=> ($_SERVER['SERVER_PORT'] == 443) 
		);
	}
	
	/**
	 * Check if TAO is already installed.
     * 
	 * @return boolean
	 */
	public static function isTAOInstalled(){
		$config = realpath(dirname(__FILE__).'/../../../generis/common/config.php');
		return (file_exists($config));
	}
    
    /**
     * Returns the availables locales (languages or cultures) of the tao platform
     * on the basis of a particular locale folder e.g.  the /locales folder of the tao
     * meta-extension.
     * 
     * A locale will be included in the resulting array only if a valid 'lang.rdf'
     * file is found.
     * 
     * @param string $path The location of the /locales folder to inspect.
     * @return array An array of strings containing the folder names contained in the /locales folder of an extension.
     * @throws UnexpectedValueException
     */
    public static function getAvailableLocales($path){
        $locales = @scandir($path);
        $returnValue = array();
        
        if ($locales !== false){
            foreach ($locales as $l){
                if ($l[0] !== '.'){
                    // We found a locale folder. Does it contain a valid lang.rdf file?
                    $langFilePath = $path . '/' . $l . '/lang.rdf';
                    if (is_file($langFilePath) && is_readable($langFilePath)){
                        try{
                            $doc = new DOMDocument('1.0', 'UTF-8');
                            $doc->load($langFilePath);
                            $xpath = new DOMXPath($doc);
                            $xpath->registerNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
                            
                            // Look for an rdf:value equals to the folder name.
                            $rdfValues = $xpath->query("//rdf:value");
                            if ($rdfValues->length == 1 && $rdfValues->item(0)->nodeValue == $l){
                                $returnValue[] = $l;
                            }
                        }
                        catch (DOMException $e){
                            // Invalid lang.rdf file, we continue to look for other ones.
                            continue;
                        }    
                    }
                }
            }

            return $returnValue;
        }else{
            throw new UnexpectedValueException("Unable to list locales in '${path}'.");
        }
    }
}
?>