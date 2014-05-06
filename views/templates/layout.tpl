<?php
use oat\tao\helpers\Template;

Template::inc('layout_header.tpl', 'tao')
?>
	<div id="main-menu" class="ui-state-default" >
		<a href="<?=_url('entry', 'Main', 'tao')?>" title="<?=__('TAO Home')?>"><span id="menu-bullet"></span></a>
		<div class="left-menu">
			<? foreach(get_data('menu') as $entry): ?>
				<span <? if (get_data('shownExtension') == $entry['extension']): ?>class="current-extension"<? endif ?>>
					<a href="<?=$entry['url']?>" title="<?=__($entry['description'])?>"><?=__($entry['name'])?></a>
				</span>
			<? endforeach ?>
		</div>

		<div class="right-menu tao-scope">
            
            
            <div>
                <a id="logout" href="<?=_url('logout', 'Main', 'tao')?>" title="<?=__('Log Out')?>">
                    <span class="icon-logout" ></span>
                </a>
			</div>
<?php if (tao_models_classes_accessControl_AclProxy::hasAccess(null, 'UserSettings', 'tao')): ?>
            <div>
                <a id="usersettings" href="<?=_url('index', 'Main', 'tao', array('structure' => 'user_settings', 'ext' => 'tao'))?>" title="<?=__('My Settings')?>">
                    <span class="icon-settings" ></span>
                </a>
                <p class="icon-desc">
                    <strong 
                      
                        class="username">
                            <?=get_data('userLabel')?>
                    </strong>
                </p>
			</div>
<? endif ?>

			<div class="vr">|</div>
            
            <? foreach(get_data('toolbar') as $action):?>
                <div>
                    <a id="<?=$action['id']?>" 
                       <? if(isset($action['js'])): ?>
                        href="#" 
                        data-action="<?=$action['js']?>"
                       <? else : ?>
                        href="<?=$action['url']?>" 
                       <? endif ?>
                       title="<?=__($action['title'])?>">
                       
                        <? if(isset($action['icon'])): ?>
                        <span class="<?=$action['icon']?>"></span>
                        <? endif ?>
                        
                        <? if(isset($action['text'])): ?>
                        <?=__($action['text'])?>
                        <? endif ?>
                        
                    </a>
                </div>
            <? endforeach ?>

            <div class="breaker"></div>
		</div>
	</div>

<? if(get_data('sections')):?>

	<div id="tabs">
		<ul>
		<?foreach(get_data('sections') as $section):?>
			<li><a id="<?=$section['id']?>" href="<?=ROOT_URL . substr($section['url'], 1) ?>" title="<?=$section['name']?>"><?=__($section['name'])?></a></li>
		<?endforeach?>
		</ul>

		<div id="sections-aside">
			<div id="section-trees"></div>
			<div id="section-actions"></div>
		</div>
		<div class="clearfix"></div>
		<div id="section-meta"></div>
	</div>

<?php
endif;
Template::inc('layout_footer.tpl', 'tao')
?>
	</body>
</html>