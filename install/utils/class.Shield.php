<?php
class tao_install_utils_Shield{
	
	protected $extensions = array();
	protected $accessFiles = array();
	
	public function __construct(array $extensions) {
		$this->extensions = $extensions;
		foreach($this->extensions as $extension){
			$file = ROOT_PATH . $extension . '/.htaccess';
			if(file_exists($file)){
				if(!is_readable($file)){
					throw new tao_install_utils_Exception("Unable to read file : $file");
				}
				$this->accessFiles[] = $file;
			}
		}
	}
	
	public function disableRewritePattern(array $patterns){

		$globalPattern = '';
		$size = count($patterns) - 1;
		foreach($patterns as $i => $pattern){
			$globalPattern .= preg_quote($pattern, '/');
			if($i < $size){
				$globalPattern .= '|';
			}
		}
		if(!empty($globalPattern)){
		
			foreach($this->accessFiles as $file){
				$lines = explode("\n", file_get_contents($file));
				$updated = 0;
				foreach($lines as $i => $line){
					if(preg_match("/".$globalPattern."/", $line)){
						$lines[$i] = '#'.$line;
						$updated++;
					}
				}
				if($updated > 0){
					if(!is_writable($file)){
						throw new tao_install_utils_Exception("Unable to write file : $file");
					}
					file_put_contents($file, implode("\n", $lines));
				}
			}
		}
	}
	
	public function protectInstall(){
		foreach($this->extensions as $extension){
			$installDir = ROOT_PATH . $extension . '/install/';
			if(file_exists($installDir) && is_dir($installDir)){
				if(!is_writable($installDir) || (file_exists($installDir . '.htaccess' && !is_writable($installDir . '.htaccess')))){
					throw new tao_install_utils_Exception("Unable to write file into : $installDir");
				}
				file_put_contents($installDir . '.htaccess', "deny from all");
			}
		}
	}
	
}
?>