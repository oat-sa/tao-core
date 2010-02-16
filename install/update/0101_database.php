<?php
 
 /* 
  * TO BE DEFINEED:
  * 
  * DATABASE_URL
  * DATABASE_LOGIN
  * DATABASE_PASS
  * DATABASE_NAME
  */
 
 if(!defined(DATABASE_NAME)){
 	echo "\nPlease configure me!\n";
	exit(1);
 }
 
 mysql_connect(DATABASE_URL, DATABASE_LOGIN, DATABASE_PASS);
 mysql_select_db(DATABASE_NAME);
 mysql_query("SET NAMES 'utf8'");
 
 
 mysql_query("alter database ".DATABASE_NAME." default CHARACTER SET utf8 COLLATE utf8_general_ci;");
 
  $counter = 0;
  $resultCounter = 0;
 
 $query = "show table status from ".DATABASE_NAME;
 $result = mysql_query($query);
 while($row = mysql_fetch_array($result, MYSQL_BOTH)){
 	if($row['Collation'] != 'utf8_general_ci'){
 		$table = $row['Name'];
 		$alterQuery = "ALTER TABLE ".$table." CHARACTER SET utf8 COLLATE utf8_general_ci";
 		$counter++;
 		if(mysql_query($alterQuery)){
 			$resultCounter++;
 			print "\n".$table." change collation to utf8_general_ci";
 		}
 	}
 }
  if($counter > 0){
 	print "\n".$resultCounter." / ".$counter." tables modified\n";
 }
 
 
 $counter = 0;
 $resultCounter = 0;
 
 $query = "SHOW TABLES";
 $result = mysql_query($query);
 while($row = mysql_fetch_array($result, MYSQL_BOTH)){
 	$table = $row[0];
 	$subQuery = "SHOW FULL COLUMNS FROM ".$table;
 	$subResult = mysql_query($subQuery);
 	while($subRow = mysql_fetch_array($subResult, MYSQL_BOTH)){
 		$type = $subRow['Type'];
 		$field = $subRow['Field'];
 		$collation = $subRow['Collation'];
 		if(preg_match("/^varchar/",$type) || preg_match("/^char/",$type) || preg_match("/^text/",$type)){
 			if($collation != 'utf8_general_ci'){
 				$counter++;
 				$alterQuery = "ALTER TABLE ".$table." MODIFY ".$field." ".$type." CHARACTER SET utf8 COLLATE utf8_general_ci";
 				if(mysql_query($alterQuery)){
 					$resultCounter++;
 					print "\n".$table.".".$field." : from ".$collation." to utf8_general_ci";
 				}
 			}
 		}
 	}
 }
 
 if($counter > 0){
 	print "\n".$resultCounter." / ".$counter." fields modified\n";
 }
 
 mysql_close();
?>