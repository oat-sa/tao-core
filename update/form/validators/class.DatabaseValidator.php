<?php
/*  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
?>
<?php

class tao_update_form_validators_DatabaseValidator
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