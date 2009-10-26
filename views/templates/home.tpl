<div id="home" class="ui-widget ui-widget-content ui-corner-all">
	<div id="home_title" class="ui-widget-header ui-corner-all">Welcome to the TAO Backend Tool</div>
	<div style="position: relative; width:50%; margin: auto; text-align:center;">
	<?foreach(get_data('extensions') as $name => $display):?>
		<div class="home-box ui-state-highlight ui-corner-all">
			<a href="<?=_url('index', null, array('extension' => $name))?>"><?=$display?></a>
		</div>
	<?endforeach?>
	</div>
</div>