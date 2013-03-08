<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title>Access Forbidden</title>
	<script type="text/javascript" src="<?= ROOT_URL ?>tao/views/js/jquery-1.8.0.min.js "></script>
	<script type="text/javascript" src="<?= ROOT_URL ?>tao/views/js/jquery-ui-1.8.23.custom.min.js"></script>

	<link rel="stylesheet" type="text/css" href="<?= ROOT_URL ?>tao/views/css/reset.css" />
	<link rel="stylesheet" type="text/css" href="<?= ROOT_URL ?>tao/views/css/custom-theme/jquery-ui-1.8.22.custom.css" />
	<link rel="stylesheet" type="text/css" href="<?= ROOT_URL ?>tao/views/css/errors.css" />
	<link rel="stylesheet" type="text/css" href="<?= ROOT_URL ?>tao/views/css/error403.css" />
</head>

<body>
	<div id="main" class="ui-widget-content ui-corner-all" style="background-image: url(<?= ROOT_URL ?>tao/views/img/errors/403.png);">
		<div id="content">
			<h1>Access Forbidden</h1>
			<p id="warning_msg">
				<img src="<?= ROOT_URL ?>tao/views/img/warning_error_tpl.png" alt="warning" class="embedWarning" />
				You have <strong>no permission</strong> to use the requested feature. If you think you should have access
				to this functionality, please <strong>try again later</strong> or <strong>if the problem remains</strong>, contact your TAO administrator to <strong>request an access</strong>.
			</p>
			<div id="redirect">
				<a href="<?= ROOT_URL ?>" id="go_to_tao_bt">TAO Home</a>
			</div>
		</div>
	</div>
</body>

</html>