<?php header("HTTP/1.0 403 Forbidden"); ?>
<html>
<head>
	<title>403 Forbidden</title>
</head>
<body>
	<b>Error:</b>
	<p><?php echo isset($message)?$message:"Access forbidden"; ?></p>
</body>
</html>