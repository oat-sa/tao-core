<?php 
 /* 
  * TO BE DEFINEED:
  * 
  * ROOT_PATH
  * DATABASE_URL
  * DATABASE_LOGIN
  * DATABASE_PASS
  * DATABASE_NAME
  */
 
 if(!defined("DATABASE_NAME")){
 	echo "\nPlease configure me!\n";
	exit(1);
 }
 
 
 $dbWrapper = core_kernel_classes_DbWrapper::singleton(DATABASE_NAME);
 
 
 
?>