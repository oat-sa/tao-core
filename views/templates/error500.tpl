<?php header("HTTP/1.0 500 Internal Server Error"); ?>
<html>
<head>
	<title>500 Internal Server Error</title>
</head>
<body>
	<b>Error:</b>
	<p><?php echo isset($message)?$message:"Page not found"; ?></p>
	<?php if(isset($trace)):?> 
		<pre>
			<?php echo $tace;?>
		</pre>
	<?php endif?>
</body>
</html>