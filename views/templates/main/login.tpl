<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>TAO</title>
	<link rel="shortcut icon" href="<?=BASE_WWW?>img/favicon.ico" type="image/x-icon" />
	
	<script type='text/javascript'>
		var imgPath = '<?=BASE_WWW?>img/';
	</script>
	
	<?=tao_helpers_Scriptloader::render()?>
	
</head>
<body>
	<div id="ajax-loading">
		<img src="<?=BASE_WWW?>img/ajax-loader.gif" alt="loading" />
	</div>

	<div id="main-menu" class="ui-state-default">&nbsp;</div>
	
	<img src="<?=BASE_WWW?>img/logo_tao.png" alt="TAO" id="logo" />
	
	<div id="home" class="ui-widget ui-widget-content ui-corner-all">
		<div id="home_title" class="ui-widget-header ui-corner-all"><?=__('TAO Backend Tool')?></div>
		
		
		<?if(get_data('errorMessage')):?>
			<div class="ui-widget ui-corner-all ui-state-error error-message">
				<?=urldecode(get_data('errorMessage'))?>
			</div>
		<?endif?>
		
		<div id="login_title" class="ui-widget ui-widget-header ui-state-default ui-corner-top">
			<?=__("Please login")?>
		</div>
		<div id="login_form" class="ui-widget ui-widget-content ui-corner-bottom">
			<?=get_data('form')?>
		</div>
		
	</div>

	
	<div id="footer">
		TAO<sup>&reg;</sup> - <?=date('Y')?> - A joint initiative of CRP Henri Tudor and the University of Luxembourg
	</div>
</body>
</html>
