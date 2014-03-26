<?php
use oat\tao\helpers\Template;
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?=get_data('title')?></title>

		<link rel="stylesheet" type="text/css" media="screen" href="<?=TAOBASE_WWW?>css/custom-theme/jquery-ui-1.8.22.custom.css"/>
		<link rel="stylesheet" type="text/css" media="screen" href="<?=TAOBASE_WWW?>css/style.css"/>
		<link rel="stylesheet" type="text/css" media="screen" href="<?=TAOBASE_WWW?>css/layout.css"/>
		<link rel="stylesheet" type="text/css" media="screen" href="<?=TAOBASE_WWW?>css/form.css"/>
		<link rel="stylesheet" type="text/css" href="<?=TAOBASE_WWW?>css/portal.css" />
		<link rel="stylesheet" type="text/css" href="<?=TAOBASE_WWW?>css/login.css" />
                
                <script id='amd-loader' 
                        type="text/javascript" 
                        src="<?=TAOBASE_WWW?>js/lib/require.js" 
                        data-main="<?=TAOBASE_WWW?>js/login"></script>
	</head>
	<body>


	<div id="portal-box" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
	    <?php if (has_data('msg')) :?>
		<span class="loginHeader">
		    <span class="hintMsg">
                        <?=get_data('msg')?>
		    </span>
		</span>
		<?php endif;?>
		<span class="loginBox">
			<?if(get_data('errorMessage')):?>
				<div class="ui-widget ui-corner-all ui-state-error error-message">
					<?=urldecode(get_data('errorMessage'))?>
				</div>
				<br />
			<?endif?>
			<div id="login-form" >
				<?=get_data('form')?>
			</div>
		</span>
	    </div>
<?php
Template::inc('layout_footer.tpl', 'tao')
?>
</body>
</html>
