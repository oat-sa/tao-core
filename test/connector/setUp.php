<?php
include_once dirname(__FILE__) . '/../../includes/raw_start.php';

$testUserData = array(
	PROPERTY_USER_LOGIN		=> 	'tjdoe',
	PROPERTY_USER_PASSWORD	=>	'test123',
	PROPERTY_USER_LASTNAME	=>	'Doe',
	PROPERTY_USER_FIRSTNAME	=>	'John',
	PROPERTY_USER_MAIL		=>	'jdoe@tao.lu',
	PROPERTY_USER_DEFLG		=>	'http://www.tao.lu/Ontologies/TAO.rdf#LangEN',
	PROPERTY_USER_UILG		=>	'http://www.tao.lu/Ontologies/TAO.rdf#LangEN',
	PROPERTY_USER_ROLES		=>  array(INSTANCE_ROLE_TAOMANAGER)
);

$testUserData[PROPERTY_USER_PASSWORD] = 'test'.rand();
		
$data = $testUserData;
$data[PROPERTY_USER_PASSWORD] = md5($data[PROPERTY_USER_PASSWORD]);
$tmclass = new core_kernel_classes_Class(CLASS_GENERIS_USER);
$user = $tmclass->createInstanceWithProperties($data);

// prepare a lookup table of languages and values
$usage = new core_kernel_classes_Resource(INSTANCE_LANGUAGE_USAGE_GUI);
$propValue = new core_kernel_classes_Property(RDF_VALUE);
$langService = tao_models_classes_LanguageService::singleton();

$lookup = array();
foreach ($langService->getAvailableLanguagesByUsage($usage) as $lang) {
	$lookup[$lang->getUri()] = (string)$lang->getUniquePropertyValue($propValue);
}

echo json_encode(array(
	'rootUrl'	=> ROOT_URL,
	'userUri'	=> $user->getUri(),
	'userData'	=> $testUserData,
	'lang'		=> $lookup	
));