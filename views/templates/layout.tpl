<? include 'layout_header.tpl' ?>

	<div id="main-menu" class="ui-state-default" >
		<a href="<?=_url('index', 'Main', 'tao')?>" title="<?=__('TAO Home')?>"><span id="menu-bullet"></span></a>
		<div class="left-menu">
			<?foreach(get_data('extensions') as $i => $extension):?>
				<span class="<? if (get_data('currentExtension') == $extension['extension']) echo 'current-extension'; if (!$extension['disabled']) echo ' disabled' ?>">
<?php if ($extension['disabled']): ?>
						<a href="<?=_url('index', null, $extension['extension'], array('structure' => $extension['id']))?>" title="<?=__($extension['description'])?>"><?=__($extension['name'])?></a>
<?php else: ?>
						<?=__($extension['name'])?>
<?php endif; ?>
					</span>
				<?if($i < (count(get_data('extensions')) - 1)):?>|<?endif?>
			<?endforeach?>
		</div>

		<div class="right-menu">
			<span>
				<a href="<?=_url('index', 'Main', 'tao')?>" title="<?=__('Home')?>">
					<img src="<?=TAOBASE_WWW?>img/home.png" alt="<?=__('Home')?>" />
				</a>
			</span>
			<span>
				<a href="<?=_url('index', 'Main', 'tao', array('structure' => 'users'))?>" title="<?=__('Users')?>">
					<img src="<?=TAOBASE_WWW?>img/users.png" alt="<?=__('Users')?>" />
				</a>
			</span>
			<span>
				<a href="<?=_url('index', 'Main', 'tao', array('structure' => 'settings'))?>" title="<?=__('Settings')?>">
					<img src="<?=TAOBASE_WWW?>img/settings.png" alt="<?=__('Settings')?>" />
				</a>
			</span>
			<span>
			<a href="#" class="file-manager" title="<?=__('Media manager')?>">
				<img src="<?=TAOBASE_WWW?>img/mediamanager.png" alt="<?=__('Media manager')?>" />
			</a>
			</span>
			<span>
				<a href="<?=_url('logout', 'Main', 'tao')?>" title="<?=__('Logout')?>">
					<img src="<?=TAOBASE_WWW?>img/logout.png" alt="<?=__('Logout')?>" />
				</a>
			</span>
		</div>
	</div>

<?if(get_data('sections')):?>

	<script type='text/javascript'>
		var currentStructure = '<?=$structure?>';
	</script>
	<div id="tabs">
		<ul>
		<?foreach(get_data('sections') as $section):?>
			<li><a id="<?=(string)$section['id']?>" href="<?=ROOT_URL.(string)$section['url']?>" title="<?=(string)$section['name']?>"><?=__((string)$section['name'])?></a></li>
		<?endforeach?>
		</ul>

		<div id="sections-aside">
			<div id="section-trees"></div>
			<div id="section-actions" ></div>
		</div>
		<div class="clearfix"></div>
		<div id="section-meta"></div>
	</div>

<?else:?>

	<?include('main/home.tpl');?>

<?endif?>

<? include 'layout_footer.tpl' ?>