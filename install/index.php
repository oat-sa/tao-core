<?php 
session_start();
session_destroy();
if(version_compare(PHP_VERSION, '5.2.6') < 0) {
	die('TAO do not support your version of PHP : ' . PHP_VERSION . ' . We recommend PHP 5.2.11');
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Language" content="en" />
<meta name="MSSmartTagsPreventParsing" content="TRUE" />
<meta name="ROBOTS" content="NOARCHIVE,NOINDEX,NOFOLLOW" />
<meta name="GOOGLEBOT" content="NOSNIPPET" />
<title>Tao Install Wizard</title>

<style type="text/css">
	@import url(./tao.css);
</style>
</head>

<body>


<div id="install">
<h1>Tao Installation Wizard</h1>
	<?php

	if(isset($_GET['message'])){
		$message = $_GET['message'];
		if (isset($message) && $message !== '') { ?>
		<div class="message">
			<p><?php echo $message ?></p>
		</div>
		<?php	
		}
	}?>
<form action="./install.php" enctype="multipart/form-data" method="post">
<fieldset><legend>Modules Installation tool</legend>


<fieldset><legend>DataBase Configuration</legend> <label for="dbhost">Database
Hostname</label> <input type="text" name="param[dbhost]" id="dbhost"
	value="<?php echo isset($_GET["dbhost"]) ? $_GET["dbhost"] : "" ;?>" />
<br />
<br />


<label for="dbuser">Database User</label> <input type="text"
	name="param[dbuser]" id="dbuser"
	value="<?php echo isset($_GET["dbuser"]) ? $_GET["dbuser"] : "" ;?>" />
<br />
<br />



<label for="dbpass">Database Password</label> <input type="password"
	name="param[dbpass]" id="dbpass"
	value="<?php echo isset($_GET["dbpass"]) ? $_GET["dbpass"] : "" ;?>" />
<br />
<br />

<label for="dbdriver">Database Driver</label> <select
	name="param[dbdriver]" id="dbdriver">
	<?php
	$combo =  array();
	$combo["mysql"]  = "MySql" ;
	$combo["Oci"] = "Oracle8i" ;

	foreach ($combo as $value => $option ) {
		$post = isset($_GET["dbdriver"]) ? $_POST["dbdriver"] : null;
		$selected = $value === $post ? ' selected="selected"' : "" ;
		echo '<option value="'.$value.'"'. $selected .'>' ;
		echo $option ;
		echo '</option>';
	}

	?>


</select></fieldset><br />

<fieldset><legend>Module Configuration </legend> <label for="moduleName">Choose a name
for this Module</label>

<input type="text" id="moduleName" name="param[moduleName]" value="<?php echo isset($_GET["moduleName"]) ? $_GET["moduleName"] : "" ;?>"/><i>The name
of the module will be the suffix of the module's namespace
(http://HOST/MODULE NAME.rdf#)</i> 

<p><label for="lg">Default Language</label> <select id="lg" name="param[lg]">
	<option value="DE">DE</option>
	<option value="EN" selected="selected">EN</option>
	<option value="FR">FR</option>
	<option value="FR">LU</option>
</select></p>

</fieldset><br />
<fieldset><legend>Super User Configuration </legend>



<p><label for="lastName">LastName</label> <input type="text"
	id="lastName" name="param[lastName]" value="<?php echo isset($_GET["lastName"]) ? $_GET["lastName"] : "" ;?>"/></p>

<p><label for="firstName">FirstName</label> <input type="text"
	id="firstName" name="param[firstName]" value="<?php echo isset($_GET["firstName"]) ? $_GET["firstName"] : "" ;?>"/></p>

<p><label for="login">Choose a login</label> <input type="text"
	id="login" name="param[login]" value="<?php echo isset($_GET["login"]) ? $_GET["login"] : "" ;?>"/> <i>Choose a login for super user </i></p>




<p><label for="pass">Choose a password</label> <input type="password"
	id="pass" name="param[pass]" value="<?php echo isset($_GET["pass"]) ? $_GET["pass"] : "" ;?>"/> <i>Choose a password for the super user, password should be at
least 6 characters long. Passwords are case sensitive</i></p>


<p><label for="passc">Confirm password</label> <input type="password"
	id="passc" name="param[passc]" value="<?php echo isset($_GET["passc"]) ? $_GET["passc"] : "" ;?>"/> <i>Re-enter password</i></p>



<p><label for="email">E-Mail</label> <input type="text" id="email" name="param[email]"
	value="<?php echo isset($_GET["email"]) ? $_GET["email"] : "" ;?>" /> <i>* optional Enter valid e-mail for this user</i></p>



<p><label for="company">Company</label> <input type="text"
	id="company" name="param[company]" value="<?php echo isset($_GET["company"]) ? $_GET["company"] : "" ;?>" /> <i>* optional Enter company for this
user</i></p>




</fieldset>
<br />
<input type="submit" class="boutonBis" name="submit"
	value="<?php echo "Install"; ?>" /></fieldset>
</form>
</div>

</body>
</html>
