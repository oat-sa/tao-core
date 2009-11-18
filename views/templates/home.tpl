<div id="home" class="ui-widget ui-widget-content ui-corner-all">
	<div id="home_title" class="ui-widget-header ui-corner-all"><?=__('TAO Backend Tool')?></div>
	<div style="position: relative; width:450px; margin: auto; text-align:center;">
	<?foreach(get_data('extensions') as $i => $extension):?>
		<div class="home-box ui-corner-all ui-widget ui-widget-header <?if($i%2==0):?>home-box-right<?endif?>">
			<img src="<?=BASE_WWW?>img/<?=$extension['extension']?>.png" /><br />
			<a href="<?=_url('index', null, array('extension' => $extension['extension']))?>"><?=$extension['name']?></a>
		</div>
	<?endforeach?>
	</div>
</div>