<?php header("HTTP/1.0 403 Forbidden"); ?>
<html>
<head>
	<title>403 Forbidden</title>
</head>
<body>
	<b>Error:</b>
	<p><?php echo isset($message)?$message:__("Access forbidden"); ?></p>
	<?php if (isset($login) && !empty($login)) { ?>
		<p><?php echo __("User name:").' '.$login; ?></p>
	<?php } ?>
</body>
</html>