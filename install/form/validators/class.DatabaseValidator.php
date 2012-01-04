<?php
require_once 'generis/includes/adodb5/adodb-exceptions.inc.php';
require_once 'generis/includes/adodb5/adodb.inc.php';

class tao_install_form_validators_DatabaseValidator 
	extends tao_helpers_form_Validator {
	
	public function __construct($options = array())
    {
		parent::__construct($options);
		
		$this->message = __('Database name already in use');
	}
	
	public function evaluate($values)
    {
		$returnValue = true;
		
		//$values = $this->getValue();
		
		if (!isset($this->options['db_host']) || !isset($this->options['db_driver']) ||
			!isset($this->options['db_name']) || !isset($this->options['db_user']) ||
			!isset($this->options['db_password'])) {
				
			// Missing option(s).
			throw new Exception("Please provide all mandatory options for the Database Validator. 'db_host', 
								'db_driver', 'db_name' and 'db_password' cannot be empty.");
		}
		else {

			// try to connect to the database.
			try {
				$host = $this->options['db_host']->getRawValue();
				$driver = $this->options['db_driver']->getRawValue();
				$dbname = $this->options['db_name']->getRawValue();
				$user = $this->options['db_user']->getRawValue();
				$password = $this->options['db_password']->getRawValue();
				$dbCreator = new tao_install_utils_DbCreator($host, $user, $password, $driver);
				
				// If the user does not want to override databases, check if the wonder database is already existing
				if(!in_array('on', $this->options['db_override']->getValues())){
					$returnValue = !$dbCreator->dbExists($dbname);
				}
			}
			catch (tao_install_utils_Exception $e) {
				// We cannot get connected to the database.
				// We assume the db is still not in use.
				$returnValue = true;
			}
			
			return $returnValue;
		}
	}
}
?>