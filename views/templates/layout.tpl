<? include 'layout_header.tpl' ?>

	<div id="main-menu" class="ui-state-default" >
		<a href="<?=_url('index', 'Main', 'tao')?>" title="<?=__('TAO Home')?>"><span id="menu-bullet"></span></a>
		<div class="left-menu">
			<?$first = true;foreach(get_data('extensions') as $extension):?>
<?php if ($extension['disabled']): ?>
				<?if($first):$first = false;?><?else:?>|<?endif?>
				<span class="<? if (get_data('currentExtension') == $extension['extension']) echo 'current-extension'; if (!$extension['disabled']) echo ' disabled' ?>">
					<a href="<?=_url('index', null, null, array('structure' => $extension['id'], 'ext' => $extension['extension']))?>" title="<?=__($extension['description'])?>"><?=__($extension['name'])?></a>
				</span>
<?php endif; ?>
			<?endforeach?>
		</div>

		<div class="right-menu">
			<span>
				<a href="<?=_url('index', 'Main', 'tao')?>" title="<?=__('Home')?>">
					<img src="<?=TAOBASE_WWW?>img/home.png" alt="<?=__('Home')?>" />
				</a>
			</span>
<?php if (tao_helpers_funcACL_funcACL::hasAccess('tao', 'Users', null)): ?>
			<span>
				<a href="<?=_url('index', 'Main', 'tao', array('structure' => 'users', 'ext' => 'tao'))?>" title="<?=__('Users')?>">
					<img src="<?=TAOBASE_WWW?>img/users.png" alt="<?=__('Users')?>" />
				</a>
			</span>
<?php endif; ?>
<?php if (tao_helpers_funcACL_funcACL::hasAccess('tao', 'Settings', null) || tao_helpers_funcACL_funcACL::hasAccess('tao', 'UserSettings', null)): ?>
			<span>
				<a href="<?=_url('index', 'Main', 'tao', array('structure' => 'settings', 'ext' => 'tao'))?>" title="<?=__('Settings')?>">
					<img src="<?=TAOBASE_WWW?>img/settings.png" alt="<?=__('Settings')?>" />
				</a>
			</span>
<?php endif; ?>
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
		var shownExtension	= '<?=$shownExtension?>';
		var shownStructure = '<?=$shownStructure?>';
	</script>
	<div id="tabs">
		<ul>
		<?foreach(get_data('sections') as $section):?>
			<li><a id="<?=$section['id']?>" href="<?=ROOT_URL.$section['url']?>" title="<?=$section['name']?>"><?=__($section['name'])?></a></li>
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