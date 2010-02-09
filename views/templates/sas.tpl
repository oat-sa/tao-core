<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>TAO</title>
	<link rel="shortcut icon" href="<?=BASE_WWW?>img/favicon.ico" type="image/x-icon" />
	
	<script type='text/javascript'>
		var imgPath = '<?=BASE_WWW?>img/';
	</script>
	
	<link rel='stylesheet' type='text/css' href='http://localhost/tao/views/css/custom-theme/jquery-ui-1.7.2.custom.css' />
	<link rel='stylesheet' type='text/css' href='http://localhost/tao/views/js/jwysiwyg/jquery.wysiwyg.css' />

	<link rel='stylesheet' type='text/css' href='http://localhost/tao/views/js/jquery.jqGrid-3.6.2/css/ui.jqgrid.css' />
	<link rel='stylesheet' type='text/css' href='http://localhost/tao/views/css/layout.css' />
	<link rel='stylesheet' type='text/css' href='http://localhost/tao/views/css/form.css' />
	<script type='text/javascript' src='http://localhost/tao/locales/EN/messages_po.js' ></script>
	<script type='text/javascript' src='http://localhost/tao/views/js/i18n.js' ></script>
	<script type='text/javascript' src='http://localhost/tao/views/js/jquery-1.3.2.min.js' ></script>
	<script type='text/javascript' src='http://localhost/tao/views/js/jquery-ui-1.7.2.custom.min.js' ></script>

	<script type='text/javascript' src='http://localhost/tao/views/js/jsTree/jquery.tree.min.js' ></script>
	<script type='text/javascript' src='http://localhost/tao/views/js/jsTree/plugins/jquery.tree.contextmenu.js' ></script>
	<script type='text/javascript' src='http://localhost/tao/views/js/jsTree/plugins/jquery.tree.checkbox.js' ></script>
	<script type='text/javascript' src='http://localhost/tao/views/js/jwysiwyg/jquery.wysiwyg.js' ></script>
	<script type='text/javascript' src='http://localhost/tao/views/js/jquery.jqGrid-3.6.2/js/i18n/grid.locale-en.js' ></script>
	<script type='text/javascript' src='http://localhost/tao/views/js/jquery.jqGrid-3.6.2/js/jquery.jqGrid.min.js' ></script>

	<script type='text/javascript' src='http://localhost/tao/views/js/jquery.numeric.js' ></script>
	<script type='text/javascript' src='http://localhost/tao/views/js/ajaxupload.js' ></script>
	<script type='text/javascript' src='http://localhost/filemanager/views/js/fmRunner.js' ></script>
	<script type='text/javascript' src='http://localhost/filemanager/views/js/jquery.fmRunner.js' ></script>
	<script type='text/javascript' src='http://localhost/tao/views/js/helpers.js' ></script>
	<script type='text/javascript' src='http://localhost/tao/views/js/uiBootstrap.js' ></script>

	<script type='text/javascript' src='http://localhost/tao/views/js/uiForm.js' ></script>
	<script type='text/javascript' src='http://localhost/tao/views/js/generis.tree.js' ></script>
	<script type='text/javascript' src='http://localhost/tao/views/js/generis.actions.js' ></script>
	<script type='text/javascript' src='http://localhost/tao/views/js/generis.treeform.js' ></script>

	
</head>
<body>
	<div id="ajax-loading">
		<img src="<?=BASE_WWW?>img/ajax-loader.gif" alt="loading" />
	</div>

	<div class="main-container">
	<? include(get_data('includedView')) ?>
	</div>
	
	<div id="footer">
		TAO<sup>&reg;</sup> - 2009 - A joint initiative of CRP Henri Tudor and the University of Luxembourg
	</div>
</body>
</html>
