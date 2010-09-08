<?if(get_data('actions')):?>
	<div id="action-title" class='ui-widget ui-widget-header ui-state-default  ui-corner-top'><?=__('Actions')?></div>
	<div id="action-items" class="ui-widget ui-widget-content ui-corner-bottom">
		<table>
			<tbody>
				<tr>
				<?$i=1;?>
				<?foreach(get_data('actions') as $action):?>
					<td>
						<div id="action_<?=$action['name']?>">
							<?if($action['disabled']):?>
								
									<img src="<?=BASE_WWW?>img/actions/<?=$action['name']?>_disabled.png"  />
									<br />
									<span><?=$action['display']?></span>
								
							<?else:?>
							
								<?if($action['js']):?>
									<a href="#" onclick="<?=$action['js']?>('<?=$action["uri"]?>', '<?=$action["classUri"]?>', '<?=$action["url"]?>')" title="<?=$action['rowName']?>"><img src="<?=BASE_WWW?>img/actions/<?=$action['name']?>.png"  /></a>
									<br />
									<a href="#" onclick="<?=$action['js']?>('<?=$action["uri"]?>', '<?=$action["classUri"]?>', '<?=$action["url"]?>')"><?=$action['display']?></a>
								<?else:?>
									<a class="<?if(!$action['reload']){?>nav<?}?>" href="<?=$action['url']?>?uri=<?=$action['uri']?>&classUri=<?=$action['classUri']?>" title="<?=$action['display']?>"><img src="<?=BASE_WWW?>img/actions/<?=$action['name']?>.png"  /></a>
									<br />
									<a class="<?if(!$action['reload']){?>nav<?}?>" href="<?=$action['url']?>?uri=<?=$action['uri']?>&classUri=<?=$action['classUri']?>"><?=$action['display']?></a>
								<?endif?>
							
							<?endif?>
						</div>
					</td>
					<?if($i > 0 && ($i % 3) == 0):?></tr><tr><?endif?>
					<?$i++;?>
				<?endforeach?>
				</tr>
			</tbody>
		</table>
	</div>
<?endif?>