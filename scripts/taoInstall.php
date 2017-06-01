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
 *               2013-2017 (update and modification) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

require_once dirname(__FILE__) . '/../install/init.php';

// Setting install details.
$installDetails = [
	'min'		=> 5,
	'parameters' => [
		[
			'name' 			=> 'db_driver',
			'type' 			=> 'string',
			'description'	=> 'Target available sgbd : pdo_pgsql, pdo_mysql, pdo_sqlsrv, pdo_oci.'
		],
		[
			'name' 			=> 'db_host',
			'type' 			=> 'string',
			'description'	=> 'Database location.'
		],
		[
			'name'			=> 'db_name',
			'type' 			=> 'string',
			'description'	=> 'The Database name corresponds to the Module name.'
		],
		[
			'name'			=> 'db_pass',
			'type' 			=> 'string',
			'required'		=> false,
			'description'	=> 'Password to access to the database.'
		],
		[
			'name'			=> 'db_user',
			'type' 			=> 'string',
			'required'		=> true,
			'description'	=> 'Login to access to the database.'
		],
	    [
	        'name'			=> 'file_path',
	        'type'			=> 'string',
	        'shortcut'		=> 'f',
	        'description'	=> 'Path to where files should be stored.'
	    ],
	    [
	        'name'			=> 'timezone',
	        'type'			=> 'string',
	        'shortcut'		=> 't',
	        'description'	=> 'Timezone of the install.'
	    ],
		[
			'name'			=> 'install_sent',
			'type' 			=> 'integer',
			'description'	=> ''
		],
		[
			'name'			=> 'module_lang',
			'type' 			=> 'string',
			'shortcut'		=> 'l',
			'description'	=> 'The default language will be used when the language parameters are not specified for the graphical interface and the data.'
		],
		[
			'name'			=> 'module_mode',
			'type' 			=> 'string',
			'description'	=> 'The deployment mode allow and deny access to resources regarding the needs of the platform.The test & development mode will enables the debugs tools, the unit tests, and the access to all the resources. The production mode is focused on the security and allow only the required resources to run TAO.'
		],
		[
			'name'			=> 'module_namespace',
			'type' 			=> 'string',
			'description'	=> 'The module\'s namespace will be used to identify the data stored by your module. Each data collected by tao is identified uniquely by an URI composed by the module namespace followed by the resource identifier (NAMESPACE#resource).'
		],
		[
			'name'			=> 'module_url',
			'type' 			=> 'string',
			'shortcut'		=> 'url',
			'required'		=> true,
			'description'	=> 'The URL to access the module from a web browser.'
		],
		[
			'name'			=> 'user_login',
			'type' 			=> 'string',
			'shortcut'		=> 'u',
			'required'		=> true,
			'description'	=> 'The login of the administrator to be created.'
		],
		[
			'name'			=> 'user_pass',
			'type' 			=> 'string',
			'shortcut'		=> 'p',
			'required'		=> true,
			'description'	=> 'The password of the administrator.'
		],
		[
		    'name'          => 'import_local',
		    'type'          => 'boolean',
		    'shortcut'      => 'i',
		    'description'   => 'States if the local.rdf files must be imported or not.'
        ],
        [
        	'name'			=> 'instance_name',
        	'type'			=> 'string',
        	'shortcut'		=> 'n',
        	'description'	=> 'The name of the instance to install.'
        ],
        [
        	'name'			=> 'extensions',
        	'type'			=> 'string',
        	'shortcut'		=> 'e',
        	'description'	=> 'Comma-separated list of extensions to install.'
        ],
        [
        	'name'			=> 'verbose',
        	'type'			=> 'boolean',
        	'shortcut'		=> 'v',
        	'description'	=> 'Verbose mode.'
        ],
        [
        	'name'			=> 'operated_by_name',
        	'type'			=> 'string',
        	'description'	=> 'Name of the organization operating the system.'
        ],
        [
        	'name'			=> 'operated_by_email',
        	'type'			=> 'string',
        	'description'	=> 'Email of the organization operating the system.'
        ],
	],
];

// Running the service.
$container->offsetSet(tao_scripts_TaoInstall::CONTAINER_INDEX, $installDetails);
new tao_scripts_TaoInstall($container);
