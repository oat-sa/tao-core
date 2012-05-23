<div id="home" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
	<div id="home-title" class="ui-widget-header ui-corner-all"><?=__('TAO Back Office')?> <?=TAO_VERSION_NAME?></div>

	<!-- JS CHECK -->
	<div id="no-js-box" class="ui-state-error">
		<?=__('Javascript is required to run this software. Please activate it in your browser.')?>
	</div>
	<script type="text/javascript">
		document.getElementById('no-js-box').style.display = 'none';
	</script>
	<div class="main-container">
		<table>
			<tbody>
				<tr>
					<?foreach(get_data('extensions') as $i => $extension):?>
					<?if($extension['extension'] != 'users'):?>
					<?if($i%4==0 && $i > 0):?>
						</tr>
						<tr>
					<?endif?>
					<td>
						<div class="home-box ui-corner-all ui-widget ui-widget-header<?php if (!$extension['disabled']) echo ' disabled' ?>" style="<?php if ($extension['disabled']) echo 'cursor:pointer;' ?>">
							<img src="<?=ROOT_URL?>/<?=$extension['extension']?>/views/img/extension.png" /><br />
<?php if ($extension['disabled']): ?>
							<a id="extension-nav-<?=$extension['extension']?>" class="extension-nav" href="<?=_url('index', 'Main', $extension['extension'], array('structure' => $extension['id']))?>"><?=__($extension['name'])?></a>
<?php else: ?>
							<span><?=__($extension['name'])?></span>
<?php endif; ?>
							<span class='extension-desc' style="display:none;"><?=__($extension['description']);?></span>
						</div>
					</td>
					<?endif?>
					<?endforeach?>
				</tr>
			</tbody>
		</table>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function(){
	$(".extension-nav").each(function(){
		var url = $(this).attr('href');
		$(this).parent("div.home-box").click(function(){
			window.location = url;
		});
	});
	$('.home-box').mouseover(function(){
		if($('.extension-desc', this).css('display') == 'none') {
			$('.extension-desc', this).show();
		}
	}).mouseout(function(){
		if($('.extension-desc', this).css('display') != 'none') {
			$('.extension-desc', this).hide();
		}
	});
});
</script>
