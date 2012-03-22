<?php
class tao_install_utils_SimpleSQLParser extends tao_install_utils_SQLParser{
	
	public function parse(){
		//common file checks
		$file = $this->getFile();
		if(!file_exists($file) || !is_readable($file) || !preg_match("/\.sql$/", basename($file))){
			throw new tao_install_utils_SQLParsingException("Wrong SQL file: $file . CHECK IT!");
		}
		
		if ($handler = fopen($file, "r")){
			
			//parse file and get only usefull lines
			$ch = "";
			while (!feof ($handler)){
				$line = utf8_decode(fgets($handler));
		
				if (isset($line[0]) && ($line[0] != '#') && ($line[0] != '-')){
					$ch = $ch.$line;
				}
			}
			
			//explode and execute
			$requests = explode(";", $ch);
			
			try{
				foreach($requests as $index => $request){
					$requestTrim = trim($request);
					if(!empty($requestTrim)){
						$this->addStatement($request);
					}
				}
			}
			catch(Exception $e){
				throw new tao_install_utils_SQLParsingException("Error executing query #$index : $request . ".$e->getMessage());
			}
			fclose($handler);
		}
	}
}
?>