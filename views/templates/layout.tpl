<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>TAO</title>
	<link rel="shortcut icon" href="<?=BASE_WWW?>img/favicon.ico" type="image/x-icon" />
	<?=tao_helpers_Scriptloader::render()?>
</head>
<body>
	<div id="ajax-loading"><img src="<?=BASE_WWW?>img/ajax-loader.gif" alt="loading" /></div>
	<img src="<?=BASE_WWW?>img/logo.gif" alt="logo" style="position: absolute; right: 10px; top: 5px;" />
	<br />
	<div id="tabs">
		<ul>
		<?foreach(get_data('extensions') as $name => $display):?>
			<li><a href="/<?=$name?>"><?=$display?></a></li>
		<?endforeach?>
		</ul>
	</div>
	<br />
	<div style="width:100%; text-align:right;">
	<span class="ui-state-default ui-corner-all" style="padding:5px;margin-right:10px;">
		<img src="<?=BASE_WWW?>img/application_double.png" />
		<a href="#" onclick="alert($tabs.tabs('option', 'selected'));"> Advanced mode</a>
	</span>
	</div>
</body>
</html>