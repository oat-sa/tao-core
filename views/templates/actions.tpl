<?if(get_data('actions')):?>
	<div id="action-title" class='ui-widget ui-state-default ui-widget-header ui-corner-top'><?=__('Actions')?></div>
	<div id="action-items" class="ui-widget ui-state-default ui-corner-bottom">
		<table>
			<tbody>
				<tr>
				<?$i=1;?>
				<?foreach(get_data('actions') as $action):?>
					<td>
						<?if($action['disabled']):?>
							<img src="<?=BASE_WWW?>img/actions/<?=$action['name']?>_disabled.png"  />
							<br />
							<span><?=$action['display']?></span>
						<?else:?>
							
							<?if($action['js']):?>
								<a href="#" onclick="<?=$action['js']?>('<?=$action["uri"]?>', '<?=$action["classUri"]?>', '<?=$action["url"]?>')" title="<?=$action['display']?>"><img src="<?=BASE_WWW?>img/actions/<?=$action['name']?>.png"  /></a>
								<br />
								<a href="#" onclick="<?=$action['js']?>('<?=$action["uri"]?>', '<?=$action["classUri"]?>', '<?=$action["url"]?>')"><?=$action['display']?></a>
							<?else:?>
								<a class="nav" href="<?=$action['url']?>" title="<?=$action['display']?>"><img src="<?=BASE_WWW?>img/actions/<?=$action['name']?>.png"  /></a>
								<br />
								<a class="nav" href="<?=$action['url']?>"><?=$action['display']?></a>
							<?endif?>
							
						
						<?endif?>
					</td>
					<?if($i > 0 && ($i % 3) == 0):?></tr><tr><?endif?>
					<?$i++;?>
				<?endforeach?>
				</tr>
			</tbody>
		</table>
	</div>
<?endif?>