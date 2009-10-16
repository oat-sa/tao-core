<?if(get_data('actions')):?>
	<ul>
		<?foreach(get_data('actions') as $action):?>
			<li class="ui-state-default ui-corner-all">
				<a class="nav" href="<?=(string)$action['url']?>"><?=(string)$action['name']?></a>
			</li>
		<?endforeach?>
	</ul>
<?endif?>