<?php
use oat\tao\helpers\Template;
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?=PRODUCT_NAME?> <?=TAO_VERSION?> Service</title>
	<link rel="shortcut icon" href="<?=BASE_WWW?>img/favicon.ico" type="image/x-icon" />

    <?=tao_helpers_Scriptloader::render()?>

    <?if(tao_helpers_Mode::is('production')):?>
        <script id='amd-loader' 
            type="text/javascript" 
            src="<?=Template::js('main.min.js', 'tao')?>" 
            data-config="<?=get_data('client_config_url')?>"></script>
    <? else: ?>
        <script id='amd-loader' 
            type="text/javascript" 
            src="<?=Template::js('lib/require.js', 'tao')?>" 
            data-main="<?=TAOBASE_WWW?>js/main"
            data-config="<?=get_data('client_config_url')?>"></script>
    <? endif ?>
	<script type='text/javascript'>
    require(['module', 'context', 'helpers', 'uiForm'], function(module, context, helpers, uiForm){	
        'use strict';
    
        var config = module.config();

        if(/edit|Edit|add/.test(context.action)){
            uiForm.initElements();
            uiForm.initOntoForms();
        } else if(/translate/.test(context.action)){
            uiForm.initElements();
            uiForm.initTranslationForm();
        } else {
            uiForm.initElements();
        }
        helpers._autoFx();

        <?if(get_data('errorMessage')):?>
            helpers.createErrorMessage("<?=get_data('errorMessage')?>");
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

<?php Template::inc(get_data('includeTemplate'), get_data('includeExtension')); ?>
	
</body>
</html>
