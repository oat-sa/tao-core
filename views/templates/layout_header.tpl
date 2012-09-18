<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?=PRODUCT_NAME?> <?=TAO_VERSION?></title>
	<link rel="shortcut icon" href="<?=BASE_WWW?>img/favicon.ico" type="image/x-icon" />

	<script type='text/javascript'>
		var jsPath 	= '<?=BASE_WWW?>js/';
		var imgPath = '<?=BASE_WWW?>img/';
		var currentExtension = '<?= context::getInstance()->getExtensionName(); ?>';
	</script>

	<script src="<?=TAOBASE_WWW?>js/require-jquery.js"></script>

	<?=tao_helpers_Scriptloader::render()?>

	<script src="<?=TAOBASE_WWW?>js/main.js"></script>

<?if(get_data('errorMessage')):?>
	<script type='text/javascript'>
		callbackMeWhenReady.loginError = function() {
				helpers.createErrorMessage("<?=get_data('errorMessage')?>");
			};
	</script>
<?endif?>
</head>
<body>
	<? include 'header.tpl' ?>

	<div id="ajax-loading" class="ui-widget-overlay"></div>