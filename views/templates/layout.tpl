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
			createErrorMessage("get_data('errorMessage')");
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
	<div id="settings-form" style="display:none;"></div>
	<div id="ajax-loading">
		<img src="<?=BASE_WWW?>img/ajax-loader.gif" alt="loading" />
	</div>

	<div id="main-menu" class="ui-state-default">
		<span><a href="<?=_url('index', null, array('extension' => 'none'))?>"><?=__('Home')?></a></span>
	<?foreach(get_data('extensions') as $extension):?>
		<?if(get_data('currentExtension') == $extension['extension']):?>
			<span class="current-extension" >
		<?else:?>
			<span>
		<?endif?>
				<a href="<?=_url('index', null, array('extension' => $extension['extension']))?>"><?=$extension['name']?></a>
			</span>
	<?endforeach?>
	
		<span><a href="<?=_url('index', 'Settings')?>" id="settings-loader"><?=__('Settings')?></a></span>
		<span><a href="<?=_url('logout')?>"><?=__('Logout')?></a></span>
	</div>
	
	<a href="<?=_url('index')?>">
		<img src="<?=BASE_WWW?>img/logo.gif" alt="logo" id="logo" />
	</a>
	
<?if(get_data('sections')):?>
	<div id="tabs">
		<ul>
		<?foreach(get_data('sections') as $section):?>
			<li><a id="<?=(string)$section['id']?>" href="<?=ROOT_URL.(string)$section['url']?>"><?=(string)$section['name']?></a></li>
		<?endforeach?>
		</ul>
		<div id="section-trees"></div>
		<div id="section-actions" ></div>
		<div id="section-meta"></div>
	</div>
<?else:?>
	<?include('home.tpl');?> 
<?endif?>

	<div id="footer">
		TAO<sup>&reg;</sup> - 2009 - A joint initiative of CRP Henri Tudor and the University of Luxembourg
	</div>
</body>
</html>