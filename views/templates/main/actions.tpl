<?if(get_data('actions')):?>
	<div id="action-title" class='ui-widget ui-widget-header ui-state-default  ui-corner-top'><?=__('Actions')?></div>
	<div id="action-items" class="ui-widget ui-widget-content ui-corner-bottom">
		<table>
			<tbody>
				<tr>
<?
	$i=1;
	foreach(get_data('actions') as $action):?>
					<td>
						<div id="action_<?=$action['name']?>">
<?
	if ($action['disabled']):
		// Snippet: determine what is the action icon depending on the current extension.
		if ($currentExtensionId !== false && file_exists(ROOT_PATH . $currentExtensionId .  '/views/img/actions/' . $action['name'] . '_disabled.png')) {
			$actionIcon = ROOT_URL . '/' . $currentExtensionId .  '/views/img/actions/' . $action['name'] . '_disabled.png';
		} else {
			$actionIcon = TAOBASE_WWW . 'img/actions/' . $action['name'] . '_disabled.png';
		}
?>
								<img src="<?=$actionIcon?>"  />
								<br />
								<span><?=$action['display']?></span>
<?
	else:
		// Snippet: determine what is the action icon depending on the current extension.
		if ($currentExtensionId !== false && file_exists(ROOT_PATH . $currentExtensionId .  '/views/img/actions/' . $action['name'] . '.png')) {
			$actionIcon = ROOT_URL . '/' . $currentExtensionId .  '/views/img/actions/' . $action['name'] . '.png';
		} else {
			$actionIcon = TAOBASE_WWW . 'img/actions/' . $action['name'] . '.png';
		}

		if ($action['js']):
?>
									<a href="#" onclick="<?=$action['js']?>('<?=$action["uri"]?>', '<?=$action["classUri"]?>', '<?=$action["url"]?>')" title="<?=$action['rowName']?>">
									<img src="<?=$actionIcon?>" />
									<br />
									<?=$action['display']?></a>
<?	else:?>
									<a class="<?if(!$action['reload']){?>nav<?}?>" href="<?=$action['url']?>?uri=<?=$action['uri']?>&classUri=<?=$action['classUri']?>" title="<?=$action['display']?>">
									<img src="<?=$actionIcon?>"  />
									<br />
									<?=$action['display']?></a>
<?	endif;
	endif?>
						</div>
					</td>
<?if($i > 0 && ($i % 3) == 0):?>
				</tr><tr>
<?endif;
$i++;
endforeach?>
				</tr>
			</tbody>
		</table>
	</div>
<?endif?>