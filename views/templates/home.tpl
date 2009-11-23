<div id="home" class="ui-widget ui-widget-content ui-corner-all">
	<div id="home_title" class="ui-widget-header ui-corner-all"><?=__('TAO Backend Tool')?></div>
	<table style="width:450px;margin:auto;">
		<tbody>
			<tr>
				<?foreach(get_data('extensions') as $i => $extension):?>
				<?if($i%3==0 && $i > 0):?>
					</tr>
					<tr>
				<?endif?>
				<td >
					<div class="home-box ui-corner-all ui-widget ui-widget-header">
						<img src="<?=BASE_WWW?>img/<?=$extension['extension']?>.png" /><br />
						<a href="<?=_url('index', null, array('extension' => $extension['extension']))?>"><?=$extension['name']?></a>
					</div>
				</td>
				<?endforeach?>
			</tr>
		</tbody>
	</table>
</div>