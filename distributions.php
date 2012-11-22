<?php
return array(
	// TAO Minimal Distribution
	array('id' => 'tao-minimal',
		  'name' => 'TAO Minimal Distribution',
		  'description' => 'The TAO Minimal Distribution provides a Computer Based Assessment Framework on which you can build specific TAO extensions.',
		  'version' => '2.4',
		  'extensions' => array('tao')),
	
	// TAO Open Source Distribution
	array('id' => 'tao-open-source',
		  'name' => 'TAO Open Source Distribution',
		  'description' => 'The TAO Open Source Distribution comes with a set of extension that makes you able to perform a full Computer Based Assmessment cycle.',
		  'version' => '2.4',
		  'extensions' => array('tao' ,'filemanager','taoItems','wfEngine','taoResults','taoTests','taoDelivery','taoGroups','taoSubjects', 'wfAuthoring'))
);
?>