<br />
<div id="home" class="ui-widget ui-widget-content ui-corner-all">
	<div id="home_title" class="ui-widget-header ui-corner-all"><?=__('TAO Backend Tool')?></div>
	
	<!-- JS CHECK -->
	<div id="no-js-box" class="ui-state-error">
		<?=__('Javascript is required to run this software. Please activate it in your browser.')?>
	</div>
	<script type="text/javascript">
		document.getElementById('no-js-box').style.display = 'none';
	</script>
	
	<table style="width:450px;margin:auto;">
		<tbody>
			<tr>
				<?foreach(get_data('extensions') as $i => $extension):?>
				<?if($extension['extension'] != 'users'):?>
				<?if($i%4==0 && $i > 0):?>
					</tr>
					<tr>
				<?endif?>
				<td>
					<div class="home-box ui-corner-all ui-widget ui-widget-header" style="cursor:pointer;">
						<img src="<?=BASE_WWW?>img/<?=$extension['extension']?>.png" /><br />
						<a class="extension-nav" href="<?=_url('index', null, null, array('extension' => $extension['extension']))?>"><?=__($extension['name'])?></a>
					</div>
				</td>
				<?endif?>
				<?endforeach?>
			</tr>
		</tbody>
	</table>
</div>
<script type="text/javascript">
$(function(){
	$(".extension-nav").each(function(){
		var url = $(this).attr('href');
		$(this).parent("div.home-box").click(function(){
			window.location = url;
		});
	});
});
</script>
