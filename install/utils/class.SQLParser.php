<?php
/**
 * An abstract SQL Parser.
 * Concrete SQL Parser for installation should implement this class.
 * 
 * @author Jerome BOGARTS <jerome.bogaerts@tudor.lu>
 */
abstract class tao_install_utils_SQLParser implements tao_install_utils_Parser {
	
	private $file;
	private $statements;
	
	/**
	 * Creates a new instance of tao_install_utils_SQLParser.
	 */
	public function __construct($file = 'unknown_path'){
		$this->setFile($file);
		$this->setStatements(array());
	}
	
	/**
	 * Sets the path to the SQL file that has to be parsed.
	 * @param string $file The file path to the SQL file to parse.
	 * @return void
	 */
	public function setFile($file){
		$this->file = $file;
	}
	
	/**
	 * Gets the path to the SQL file that has to be parsed.
	 * @return string The the path the SQL file that has to be parsed.
	 */
	public function getFile(){
		return $this->file;
	}
	
	/**
	 * Sets the array of string that represents the parsed SQL statements.
	 * @param array $statements an array of SQL statements as strings.
	 * @return void
	 */
	protected function setStatements(array $statements){
		$this->statements = $statements;
	}
	
	/**
	 * Gets the array of SQL statements parsed by the parser. It is empty
	 * until the first call of the parse() method or if no SQL statements
	 * were found.
	 * @return array an array of SQL statements as string.
	 */
	public function getStatements(){
		return $this->statements;
	}
	
	/**
	 * Adds a statement to the collection of parsed SQL statements.
	 * @param string $statement The SQL statement to add.
	 * @return void
	 */
	protected function addStatement($statement){
		array_push($this->statements, $statement);
	}
}
?>