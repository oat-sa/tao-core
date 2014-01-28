<div id="home" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
	<div id="home-title" class="ui-widget-header ui-corner-all"><?=__('TAO Back Office')?> <?=TAO_VERSION_NAME?></div>
	<!-- JS CHECK -->
	<div id="no-js-box" class="ui-state-error">
		<?=__('Javascript is required to run this software. Please activate it in your browser.')?>
	</div>
	<script type="text/javascript">
        document.getElementById('no-js-box').style.display = 'none';
        var isFirstTimeLogin = <?=get_data('isFirstTimeLogin')?>;
	</script>
    <div class="splash-screen-wrapper tao-scope">
        <div id="splash-screen" class="modal splash-modal">
			<div class="modal-title">
				<h1>Get started with TAO</h1>
				<span class="title-shadow"></span>
			</div>
            <ul class="modal-nav plain clearfix">
                <li class="active"><a href="javascript:void(0);" data-panel="overview">Overview</a></li>
                <li><a href="javascript:void(0);" data-panel="videos">Videos</a></li>
            </ul>
			<div class="modal-content">
				<div class="panels" data-panel-id="overview" style="display: block;">
					<p>Discover how easy it is to create an assessment with TAO!</p>
					<div class="diagram">
                        <?	$i = 1;
                            foreach(get_data('defaultExtensions') as $extension):
                        ?>
                            <a href="javascript:void(0);" class="module <?=$extension['id']?>-wrapper <?php if (!$extension['enabled']) echo ' disabled' ?>" data-module-name="<?=$extension['id']?>">
                                <span class="module-title"><?=__($extension['name'])?></span>
                                <span class="icon icon-<?=$extension['id']?>"></span>
                                <?php if (!$extension['enabled']) echo '<span class="icon locked"></span>' ?>
                            </a>
                            <?php if ($i<6) echo "<span class=\"arrow icon-arrow-".$i." arrow-".$i."-wrapper\"></span>" ?>
                        <?  $i++;
                        endforeach?>
					</div>
					<div class="desc clearfix">
						<div class="module-desc default">
							<span>Select an icon on the left to learn more about this step.<span/>
						</div>
                        <?	
                         foreach(get_data('extensions') as $extension):
                        ?>
                            <div class="module-desc" data-module="<?=$extension['id']?>">
                                <span class="icon"></span>
                                <h2><?=__($extension['name'])?></h2>
                                <?=__($extension['description']);?>
                            </div>
                        <?endforeach?>
					</div>
                    <?
                        $moreShowed = false;
                        foreach(get_data('additionalExtensions') as $extension):
                    ?>
                    <?if(!$moreShowed) echo '<span class="more">More:</span>';?>
                        <a href="javascript: void(0);" class="module new-module" data-module-name="<?=$extension['id']?>">
                            <span class="icon-small"></span>
                            <?=__($extension['name'])?>
                        </a>
                    <?      $moreShowed = true;
                        endforeach?>
				</div>
				<div class="panels" data-panel-id="videos">
				</div>
			</div>
			<div class="modal-footer">
				<button id="splash-close-btn" class="btn-button" type="button">Close & Start using TAO!</button>
				<div class="checkbox-wrapper">
					<input type="checkbox" id="dontshow"/>
					<label for="dontshow" class="checkbox-label">Do not show this window when TAO opens.</label>
				</div>
				<div class="note">Note: You can access this overview whenever you need via the Help icon.</div>
			</div>
		</div>
    </div>
	<div class="main-container">
		<table>
			<tbody>
				<tr>
					<?	$i = 0;
						foreach(get_data('extensions') as $extension):
					?>
					<?if($i%4==0 && $i > 0):?>
						</tr>
						<tr>
					<?endif?>
					<td>
						<div class="home-box ui-corner-all ui-widget ui-widget-header<?php if (!$extension['enabled']) echo ' disabled' ?>" style="<?php if ($extension['enabled']) echo 'cursor:pointer;' ?>">
							<img src="<?=ROOT_URL?>/<?=$extension['extension']?>/views/img/extension.png" /><br />
<?php if ($extension['enabled']): ?>
							<a id="extension-nav-<?=$extension['extension']?>" class="extension-nav" href="<?=_url('index', null, null, array('structure' => $extension['id'], 'ext' => $extension['extension']))?>"><?=__($extension['name'])?></a>
<?php else: ?>
							<span><?=__($extension['name'])?></span>
<?php endif; ?>
							<span class='extension-desc' style="display:none;"><?=__($extension['description']);?></span>
						</div>
					</td>
					<?$i++;endforeach?>
				</tr>
			</tbody>
		</table>
	</div>
</div>