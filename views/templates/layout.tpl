<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>TAO</title>
	<link rel="shortcut icon" href="<?=BASE_WWW?>img/favicon.ico" type="image/x-icon" />
	
	<script type='text/javascript'>
		var jsPath 	= '<?=BASE_WWW?>js/';
		var imgPath = '<?=BASE_WWW?>img/';
	</script>
	
	<?=tao_helpers_Scriptloader::render()?>
	
	<script type='text/javascript'>
		$(function(){
		
		<?if(get_data('errorMessage')):?>
			createErrorMessage("<?=get_data('errorMessage')?>");
		<?endif?>
		
		});
	</script>
</head>
<body>
	<?if(get_data('message')):?>
	<div id="info-box" class="ui-widget-header ui-corner-all auto-slide">
		<span><?=get_data('message')?></span>
	</div>
	<?endif?>
	<div id="ajax-loading">
		<img src="<?=BASE_WWW?>img/ajax-loader.gif" alt="loading" />
	</div>
	<div id="menu-popup" style="display:none;">
		<ul>
			<li><a href="<?=_url('index', null, array('extension' => 'none'))?>"><img src="<?=BASE_WWW?>img/home.png" class="icon" /><?=__('Home')?></a></li>
			<li><a href="<?=_url('index', null, array('extension' => 'users'))?>"><img src="<?=BASE_WWW?>img/user_edit.png" class="icon" /><?=__('Users')?></a></li>
		<?if(get_data('currentExtension') && get_data('currentExtension') != 'users'):?>
			<li><a href="<?=_url('index', 'Settings')?>" class="settings-loader"><img src="<?=BASE_WWW?>img/settings.png" class="icon" /><?=__('Settings')?></a></li>
		<?else:?>
			<li><img src="<?=BASE_WWW?>img/settings_disabled.png" class="icon" /><?=__('Settings')?></li>
		<?endif?>
			<li><a href="<?=_url('logout')?>"><img src="<?=BASE_WWW?>img/logout.png" class="icon" /><?=__('Logout')?></a></li>
		</ul>
	</div>
	
	<div id="main-menu" class="ui-state-default" >
		<img id="menu-button" src='<?=BASE_WWW?>img/tao_button.png' alt='tao' />
		
		<span class="ghost-menu"><a href="<?=_url('index', null, array('extension' => 'none'))?>"><?=__('Home')?></a></span>
	<?foreach(get_data('extensions') as $extension):?>
		<?if(get_data('currentExtension') == $extension['extension']):?>
			<span class="current-extension">
		<?else:?>
			<span>
		<?endif?>
				<a href="<?=_url('index', null, array('extension' => $extension['extension']))?>"><?=__($extension['name'])?></a>
			</span>
	<?endforeach?>
	<span class="ghost-menu"><a href="<?=_url('index', null, array('extension' => 'users'))?>"><?=__('Users')?></a></span>
	<?if(get_data('currentExtension') && get_data('currentExtension') != 'users'):?>
		<span class="ghost-menu"><a href="<?=_url('index', 'Settings')?>" class="settings-loader"><?=__('Settings')?></a></span>
	<?else:?>
		<span class="ghost-menu disabled-extension"><?=__('Settings')?></span>
	<?endif?>
		<span class="ghost-menu"><a href="<?=_url('logout')?>"><?=__('Logout')?></a></span>
	
		<img id="menu-expander" src="<?=BASE_WWW?>img/arrow_right.png" />
	</div>
	<img src="<?=BASE_WWW?>img/logo_tao.png" alt="TAO" id="logo" />
	
<?if(get_data('sections')):?>
	<div id="tabs">
		<ul>
		<?foreach(get_data('sections') as $section):?>
			<li><a id="<?=(string)$section['id']?>" href="<?=ROOT_URL.(string)$section['url']?>"><?=__((string)$section['name'])?></a></li>
		<?endforeach?>
		</ul>
		<div id="section-trees"></div>
		<div id="section-actions" ></div>
		<div id="section-meta"></div>
	</div>
<?else:?>
	<?include('home.tpl');?> 
<?endif?>

	<div id="section-lg">
		<img src="<?=BASE_WWW?>img/lg.png" />&nbsp;<?=__('Data language')?>: <strong><?=__(get_data('user_lang'))?></strong> 
	</div>
	<div id="footer">
		TAO<sup>&reg;</sup> - 2009 - A joint initiative of CRP Henri Tudor and the University of Luxembourg
	</div>
</body>
</html>