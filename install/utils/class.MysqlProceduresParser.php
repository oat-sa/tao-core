<?php
/**
 * This SQL Parser is able to deal with Stored Procedures and Functions
 * for mySQL server in a compliant SQL file. The following statements are supported.
 * 
 * - DROP PROCEDURE
 * - DROP FUNCTION
 * - CREATE PROCEDURE
 * - CREATE FUNCTION
 * 
 * @author Jerome BOGAERTS <jerome.bogaerts@tudor.lu>
 *
 */
class tao_install_utils_MysqlProceduresParser extends tao_install_utils_SQLParser{
	
	/**
	 * Parse a SQL file containing mySQL compliant Procedures or Functions.
	 * @return void
	 * @throws tao_install_utils_SQLParsingException
	 */
	public function parse(){
		$this->setStatements(array());
		$file = $this->getFile();
		
		if (!file_exists($file)){
			throw new tao_install_utils_SQLParsingException("SQL file '${file}' does not exist.");
		}
		else if (!is_readable($file)){
			throw new tao_install_utils_SQLParsingException("SQL file '${file}' is not readable.");
		}
		else if(!preg_match("/\.sql$/", basename($file))){
			throw new tao_install_utils_SQLParsingException("File '${file}' is not a valid SQL file. Extension '.sql' not found.");
		}
		
		$content = @file_get_contents($file);
		if ($content !== false){
			$matches = array();
			$patterns = array(	'DROP\s+FUNCTION\s+IF\s+EXISTS\s*\w+\s*;',
								'DROP\s+PROCEDURE\s+IF\s+EXISTS\s*\w+\s*;',
								'CREATE\s+PROCEDURE\s+\w+\s*\(.*\)\s*(?:(?:NOT\s+){0,1}DETERMINISTIC){0,1}\s*BEGIN\s+(?:.*\s*;\s*)*END\s*;',
								'CREATE\s+FUNCTION\s+\w+\s*\(.*\)\s*RETURNS\s+(?:.*)\s*(?:(?:NOT\s+){0,1}DETERMINISTIC){0,1}\s*BEGIN\s+(?:.*\s*;\s*)*END\s*;');
							
			if (preg_match_all('/' . implode($patterns, '|') . '/i', $content, $matches)){
				foreach ($matches[0] as $match){
					$this->addStatement($match);
				}
			}
		}
		else{
			throw new tao_install_utils_SQLParsingException("SQL file '${file}' cannot be read. An unknown error occured while reading it.");	
		}
	}
}
?>