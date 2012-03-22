<?php
/**
 * Installation components that claims to be able to parse
 * something should implement this interface.
 * 
 * @author Jerome BOGAERTS
 */
interface tao_install_utils_Parser {
	
	/**
	 * Parse something.
	 * @return void
	 * @throws tao_install_utils_ParsingException
	 */
	public function parse();
}
?>