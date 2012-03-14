<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?=PRODUCT_NAME?> <?=TAO_VERSION?> Service</title>
	<link rel="shortcut icon" href="<?=BASE_WWW?>img/favicon.ico" type="image/x-icon" />

	<script type='text/javascript'>
		var jsPath 	= '<?=BASE_WWW?>js/';
		var imgPath = '<?=BASE_WWW?>img/';
	</script>

	<?=tao_helpers_Scriptloader::render()?>

	<script type='text/javascript'>
		var ctx_extension 	= '<?=get_data("extension")?>';
		var ctx_module 		= '<?=get_data("module")?>';
		var ctx_action 		= '<?=get_data("action")?>';
		$(document).ready(function(){
			if(/edit|Edit|add/.test(ctx_action)){
				uiForm.initElements();
				uiForm.initOntoForms();
			}
			else if(/translate/.test(ctx_action)){
				uiForm.initElements();
				uiForm.initTranslationForm();
			}
			else{
				uiForm.initElements();
			}
			_autoFx();
		});
	</script>
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
	
	<div id="ajax-loading" class="ui-widget-overlay">
		<img src="<?=TAOBASE_WWW?>img/ajax-loader.gif" alt="Loading..." />
	</div>

	<div id="tabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all ui-tabs-collapsible">
		<div id="sas-widget" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
			<? include(get_data('includedView')); ?>
		</div>
	</div>

</body>
</html>
