<?php 

error_reporting(E_ALL);

$dbWarpper = core_kernel_classes_DbWrapper::singleton(DATABASE_NAME);

$generisUserClass = new core_kernel_classes_Class(CLASS_GENERIS_USER);
$classRole = new core_kernel_classes_Class(CLASS_ROLE);
$classTaoManager = new core_kernel_classes_Class('http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole');





$loginProp = new core_kernel_classes_Property(PROPERTY_USER_LOGIN);
$passProp = new core_kernel_classes_Property(PROPERTY_USER_PASSWORD);
$defLgProp = new core_kernel_classes_Property(PROPERTY_USER_DEFLG);
$firstNameProp = new core_kernel_classes_Property(PROPERTY_USER_FIRTNAME);
$mailProp = new core_kernel_classes_Property(PROPERTY_USER_MAIL);
$lastNameProp = new core_kernel_classes_Property(PROPERTY_USER_LASTNAME);
$uiLgProp = new core_kernel_classes_Property(PROPERTY_USER_UILG);

//Migrate previous backoffice user
$result = $dbWarpper->execSql('select * from user');
while (!$result-> EOF){
	$newUserInstance = $classTaoManager->createInstance('User_'.$result->fields['login'],'Generated during update from user table on'. date(DATE_ISO8601));
	echo 'Migrate Manager '. 'User_'.$result->fields['login'] . '<br/>';
	$newUserInstance->setPropertyValue($loginProp,$result->fields['login']);
	$newUserInstance->setPropertyValue($passProp,$result->fields['password']);
	$newUserInstance->setPropertyValue($lastNameProp,$result->fields['LastName']);
	$newUserInstance->setPropertyValue($firstNameProp,$result->fields['FirstName']);
	$newUserInstance->setPropertyValue($mailProp,$result->fields['E_Mail']);
	$newUserInstance->setPropertyValue($defLgProp,$result->fields['Deflg']);
	$newUserInstance->setPropertyValue($uiLgProp,$result->fields['Uilg']);
	$result->MoveNext();
}


//Migrate previous Subject user
$taoSubjectClass = new core_kernel_classes_Class('http://www.tao.lu/Ontologies/TAOSubject.rdf#Subject');
$subjectInstancesArray = $taoSubjectClass->getInstances(true);
$subjectLoginProp = new core_kernel_classes_Property('http://www.tao.lu/Ontologies/TAOSubject.rdf#Login');
$subjectPassProp = new core_kernel_classes_Property('http://www.tao.lu/Ontologies/TAOSubject.rdf#Password');

foreach ($subjectInstancesArray as $subject) {
	$newLogin = $subject->getOnePropertyValue($subjectLoginProp);
	$newPass = $subject->getOnePropertyValue($subjectPassProp);
	if(!core_kernel_users_Service::loginExists($newLogin)) {
		echo 'Migrate Subject '. $newLogin . '<br/>';
		$subject->editPropertyValues($loginProp,$newLogin);
		$subject->editPropertyValues($passProp,md5($newPass));
		$subject->removePropertyValues($subjectLoginProp);
		$subject->removePropertyValues($subjectPassProp);
	}
}
$result = $dbWarpper->execSql("DELETE FROM `statements` WHERE `subject` ='http://www.tao.lu/Ontologies/TAOSubject.rdf#Login' OR
 `subject` ='http://www.tao.lu/Ontologies/TAOSubject.rdf#Password';");


$wfUserClass =  new core_kernel_classes_Class('http://www.tao.lu/middleware/taoqual.rdf#i11859665003194');
$wfUserInstancesArray = $wfUserClass->getInstances(true);
$wfUserLoginProp = new core_kernel_classes_Property('http://www.tao.lu/middleware/taoqual.rdf#i119012256329986');
$wfUserPassProp = new core_kernel_classes_Property('http://www.tao.lu/middleware/taoqual.rdf#i119012711429320');
$wfUserMailProp = new core_kernel_classes_Property('http://www.tao.lu/middleware/taoqual.rdf#i120593879614028');
$wfUserRoleProp = new core_kernel_classes_Property('http://www.tao.lu/middleware/taoqual.rdf#i119012169222836');

$backOfficeClass = new core_kernel_classes_Class('http://www.tao.lu/Ontologies/TAO.rdf#BackOffice');

	


foreach($wfUserInstancesArray as $wfUser){

	$role = $wfUser->getOnePropertyValue($wfUserRoleProp); 

	if($role instanceof core_kernel_classes_Resource) {
		$role->setPropertyValue(new core_kernel_classes_Property(RDF_TYPE),$backOfficeClass->uriResource);
	
		$role->editPropertyValues(new core_kernel_classes_Property(RDF_SUBCLASSOF),CLASS_GENERIS_USER);
		
		$roleClass = new core_kernel_classes_Class($role->uriResource);
		$newLogin = $wfUser->getOnePropertyValue($wfUserLoginProp); 
		if(!core_kernel_users_Service::loginExists($newLogin)) {
			echo 'Migrate wfUser '. $newLogin . '<br/>';
			$newPass = $wfUser->getOnePropertyValue($wfUserPassProp); 	
			$newMail =  $wfUser->getOnePropertyValue($wfUserMailProp); 	
			$newWfUserInstance = $roleClass->createInstance('wfUser_'. $newLogin,'Generated during update from user table on'. date(DATE_ISO8601));
			$newWfUserInstance->setPropertyValue($loginProp,$newLogin);
			$newWfUserInstance->setPropertyValue($passProp,md5($newPass));
			$newUserInstance->setPropertyValue($mailProp,$newMail);
		}
		else{
			
		}
	}
	$wfUser->delete();
	
	$result = $dbWarpper->execSql("DELETE FROM `statements` WHERE `subject` ='". $wfUser->uriResource. "';");

}
$dbWarpper->execSql("DELETE FROM `statements` WHERE `subject` ='". $wfUserClass->uriResource. "';");
$dbWarpper->execSql("DELETE FROM `statements` WHERE `subject` ='". $wfUserLoginProp->uriResource. "';");
$dbWarpper->execSql("DELETE FROM `statements` WHERE `subject` ='". $wfUserPassProp->uriResource. "';");
$dbWarpper->execSql("DELETE FROM `statements` WHERE `subject` ='". $wfUserClass->uriResource. "';");
$dbWarpper->execSql("DELETE FROM `statements` WHERE `subject` ='". $wfUserMailProp->uriResource. "';");
$dbWarpper->execSql("DELETE FROM `statements` WHERE `subject` ='". $wfUserRoleProp->uriResource. "';");
$dbWarpper->execSql("DELETE FROM `statements` WHERE `subject` ='". $wfUserRoleProp->uriResource. "';");


echo 'done';
?>