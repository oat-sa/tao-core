<?php
use oat\tao\helpers\Template;
if(get_data('actions')):
?>
<div id="action-title" class='ui-widget ui-widget-header ui-state-default container-title  ui-corner-top'><?=__('Actions')?></div>
	<div id="action-items" class="ui-widget ui-widget-content ui-corner-bottom">
	<?php foreach(get_data('actions') as $action):?>
		<div id="action_<?=$action['name']?>">
		<?php if($action['disabled']):
			// Snippet: determine what is the action icon depending on the current extension.
			$iconExt = get_data('shownExtension') !== false && file_exists(ROOT_PATH .  $action['ext']  .  '/views/img/actions/' . $action['name'] . '_disabled.png')
                ? $action['ext']
                : 'tao';
        ?>
			<img src="<?= Template::img('actions/' . $action['name'] . '_disabled.png', $iconExt) ?>" /><br />
			<span><?=$action['display']?></span>
		<?php else:
			// Snippet: determine what is the action icon depending on the current extension.
			$iconExt = (get_data('shownExtension') !== false && file_exists(ROOT_PATH . $action['ext'] .  '/views/img/actions/' . $action['name'] . '.png'))
                ? $action['ext']
                : 'tao';
			if($action['js']):
            ?>
				<a href="<?=$action['url']?>" data-action="<?=$action['js']?>" data-uri="<?=$action['uri']?>" data-class-uri="<?=$action['classUri']?>" title="<?=$action['rowName']?>">
            <?php else: ?>
				<a class="<?if(!$action['reload']){?>nav<?}?>" href="<?=$action['url']?>?uri=<?=$action['uri']?>&classUri=<?=$action['classUri']?>" title="<?=$action['display']?>">
            <?endif;?>
			<img src="<?= Template::img('actions/' . $action['name'] . '.png', $iconExt) ?>" /><br />
			<?=$action['display']?></a>
		<?endif;?>
	</div>
	<?endforeach;?>
</div>
<?endif;?>
