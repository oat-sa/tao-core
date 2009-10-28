<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>TAO</title>
	<link rel="shortcut icon" href="<?=BASE_WWW?>img/favicon.ico" type="image/x-icon" />
	<?=tao_helpers_Scriptloader::render()?>
</head>
<body>
	<div id="settings-form" style="display:none;"></div>
	<div id="ajax-loading">
		<img src="<?=BASE_WWW?>img/ajax-loader.gif" alt="loading" />
	</div>

	<div id="main-menu" class="ui-state-default">
	
	<?foreach(get_data('extensions') as $name => $display):?>
		<?if(get_data('currentExtension') == $name):?>
			<span class="current-extension" >
		<?else:?>
			<span>
		<?endif?>
				<a href="<?=_url('index', null, array('extension' => $name))?>"><?=$display?></a>
			</span>
	<?endforeach?>
	
		<span><a href="<?=_url('index', 'Settings')?>" id="settings-loader"><?=__('Settings')?></a></span>
		<span><a href="<?=_url('logout')?>"><?=__('Logout')?></a></span>
	</div>
	
	<img src="<?=BASE_WWW?>img/logo.gif" alt="logo" id="logo" />
	
<?if(get_data('sections')):?>
	<div id="tabs">
		<ul>
		<?foreach(get_data('sections') as $section):?>
			<li><a href="<?=ROOT_URL.(string)$section['url']?>"><?=(string)$section['name']?></a></li>
		<?endforeach?>
		</ul>
		<div id="section-trees"></div>
		<div id="section-actions" ></div>
		<div id="section-grid" ></div>
	</div>
<?else:?>
	<?include('home.tpl');?> 
<?endif?>

	<br />
</body>
</html>