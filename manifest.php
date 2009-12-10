<?php
	return array(
		'name' => 'TAO',
		'description' => 'TAO http://www.tao.lu',
		'additional' => array(
			'version' => '1.0',
			'author' => 'CRP Henry Tudor',
			'dependances' => array(),
			'install' => array( 
				'sql' => dirname(__FILE__). '/install/db/tao',
				'php' => dirname(__FILE__). '/install/install.php'
			),
			'configFile' => dirname(__FILE__). '/includes/common.php',
			'classLoaderPackages' => array( 
				dirname(__FILE__).'/actions/',
				dirname(__FILE__).'/helpers/',
				dirname(__FILE__).'/helpers/form'
			 )
			

				
			
		)
	);
?>