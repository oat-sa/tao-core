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
	<option value="AA">AA</option>
	<option value="AB">AB</option>
	<option value="AF">AF</option>
	<option value="AM">AM</option>
	<option value="AR">AR</option>
	<option value="AS">AS</option>
	<option value="AY">AY</option>
	<option value="AZ">AZ</option>
	<option value="BA">BA</option>
	<option value="BE">BE</option>
	<option value="BG">BG</option>
	<option value="BH">BH</option>
	<option value="BI">BI</option>
	<option value="BN">BN</option>
	<option value="BO">BO</option>
	<option value="BR">BR</option>
	<option value="CA">CA</option>
	<option value="CO">CO</option>
	<option value="CS">CS</option>
	<option value="CY">CY</option>
	<option value="DA">DA</option>
	<option value="DE">DE</option>
	<option value="DZ">DZ</option>
	<option value="EL">EL</option>
	<option value="EN" selected="selected">EN</option>
	<option value="EO">EO</option>
	<option value="ES">ES</option>
	<option value="ET">ET</option>
	<option value="EU">EU</option>
	<option value="FA">FA</option>
	<option value="FI">FI</option>
	<option value="FJ">FJ</option>
	<option value="FO">FO</option>
	<option value="FR">FR</option>
	<option value="FY">FY</option>
	<option value="GA">GA</option>
	<option value="GD">GD</option>
	<option value="GL">GL</option>
	<option value="GN">GN</option>
	<option value="GU">GU</option>
	<option value="HA">HA</option>
	<option value="HI">HI</option>
	<option value="HR">HR</option>
	<option value="HU">HU</option>
	<option value="HY">HY</option>
	<option value="IA">IA</option>
	<option value="IE">IE</option>
	<option value="IK">IK</option>
	<option value="IN">IN</option>
	<option value="IS">IS</option>
	<option value="IT">IT</option>
	<option value="IW">IW</option>
	<option value="JA">JA</option>
	<option value="JI">JI</option>
	<option value="JW">JW</option>
	<option value="KA">KA</option>
	<option value="KK">KK</option>
	<option value="KL">KL</option>
	<option value="KM">KM</option>
	<option value="KN">KN</option>
	<option value="KO">KO</option>
	<option value="KS">KS</option>
	<option value="KU">KU</option>
	<option value="KY">KY</option>
	<option value="LA">LA</option>
	<option value="LN">LN</option>
	<option value="LO">LO</option>
	<option value="LT">LT</option>
	<option value="LV">LV</option>
	<option value="MG">MG</option>
	<option value="MI">MI</option>
	<option value="MK">MK</option>
	<option value="ML">ML</option>
	<option value="MN">MN</option>
	<option value="MO">MO</option>
	<option value="MR">MR</option>
	<option value="MS">MS</option>
	<option value="MT">MT</option>
	<option value="MY">MY</option>
	<option value="NA">NA</option>
	<option value="NE">NE</option>
	<option value="NL">NL</option>
	<option value="NO">NO</option>
	<option value="OC">OC</option>
	<option value="OM">OM</option>
	<option value="OR">OR</option>
	<option value="PA">PA</option>
	<option value="PL">PL</option>
	<option value="PS">PS</option>
	<option value="PT">PT</option>
	<option value="QU">QU</option>
	<option value="RM">RM</option>
	<option value="RN">RN</option>
	<option value="RO">RO</option>
	<option value="RU">RU</option>
	<option value="RW">RW</option>
	<option value="SA">SA</option>
	<option value="SD">SD</option>
	<option value="SG">SG</option>
	<option value="SH">SH</option>
	<option value="SI">SI</option>
	<option value="SK">SK</option>
	<option value="SL">SL</option>
	<option value="SM">SM</option>
	<option value="SN">SN</option>
	<option value="SO">SO</option>
	<option value="SQ">SQ</option>
	<option value="SR">SR</option>
	<option value="SS">SS</option>
	<option value="ST">ST</option>
	<option value="SU">SU</option>
	<option value="SV">SV</option>
	<option value="SW">SW</option>
	<option value="TA">TA</option>
	<option value="TE">TE</option>
	<option value="TG">TG</option>
	<option value="TH">TH</option>
	<option value="TI">TI</option>
	<option value="TK">TK</option>
	<option value="TL">TL</option>
	<option value="TN">TN</option>
	<option value="TO">TO</option>
	<option value="TR">TR</option>
	<option value="TS">TS</option>
	<option value="TT">TT</option>
	<option value="TW">TW</option>
	<option value="UK">UK</option>
	<option value="UR">UR</option>
	<option value="UZ">UZ</option>
	<option value="VI">VI</option>
	<option value="VO">VO</option>
	<option value="WO">WO</option>
	<option value="XH">XH</option>
	<option value="YO">YO</option>
	<option value="ZH">ZH</option>
	<option value="ZU">ZU</option>
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
