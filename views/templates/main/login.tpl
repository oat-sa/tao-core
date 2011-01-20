<? include TAO_TPL_PATH .'layout_header.tpl' ?>

	<div id="main-menu" class="ui-state-default"></div>
	<div id="home" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
		<div id="home-title" class="ui-widget-header ui-corner-all"><?=__('TAO Back Office')?>  <?=TAO_VERSION_NAME?></div>
		
		<div id="login-box">
			<div id="login-lpanel">
				<img src="<?=BASE_WWW?>img/tao_logo_big.png" alt="tao"/>
			</div>
			<div id="login-rpanel">
				<?if(get_data('errorMessage')):?>
					<div class="ui-widget ui-corner-all ui-state-error error-message">
						<?=urldecode(get_data('errorMessage'))?>
					</div>
				<?endif?>
				
				<div id="login-title" class="ui-widget ui-widget-header ui-state-default ui-corner-top">
					<?=__("Please login")?>
				</div>
				<div id="login-form" class="ui-widget ui-widget-content ui-corner-bottom">
					<?=get_data('form')?>
				</div>
			</div>
			
		</div>
		
	</div>

<? include TAO_TPL_PATH .'layout_footer.tpl' ?>