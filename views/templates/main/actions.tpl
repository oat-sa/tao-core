<?if(get_data('actions')):?>
<div id="action-title" class='ui-widget ui-widget-header ui-state-default container-title  ui-corner-top'><?=__('Actions')?></div>
	<ul class="actions-bar">
	<?foreach(get_data('actions') as $action):?>
         <li class="btn-info small action" data-context="<?=$action['context']?>" title="<?=$action['display']?>" data-action="<?=$action['binding']?>">
            <a href="<?=$action['url']?>"><span class="icon-email"></span><?=$action['display']?></a>
         </li>
	<?endforeach;?>
    </ul>
</div>
<?endif;?>
