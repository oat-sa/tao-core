<div id="home" class="ui-widget ui-widget-content ui-corner-all">
	<div id="home_title" class="ui-widget-header ui-corner-all">Welcome to the TAO Backend Tool</div>
	<div style="position: relative; width:450px; margin: auto; text-align:center;">
	<?$i=0;?>
	<?foreach(get_data('extensions') as $name => $display):?>
		<div class="home-box ui-corner-all ui-widget ui-widget-header <?if($i%2==0):?>home-box-right<?endif?>">
			<img src="<?=BASE_WWW?>img/<?=$name?>.png" /><br />
			<a href="<?=_url('index', null, array('extension' => $name))?>"><?=$display?></a>
		</div>
	<?$i++;?>
	<?endforeach?>
	</div>
</div>