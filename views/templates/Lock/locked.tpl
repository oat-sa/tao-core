<?php
use oat\tao\helpers\Template;
?>
<header class="section-header flex-container-full">
    <h2><?=__('Resource Locked')?></h2>
</header>
<div class="flex-container-half">

    <div class="feedback feedback-warning">
		<h3><span class="icon-lock big"></span> <?=__('This resource is currently being edited and has been locked')?></h3>
		<div class="grid-row">
		    <span class="col-2"><?=__('Resource:')?></span>
            <span class="col-10"><?=get_data('label')?></span>
        </div>
		<div class="grid-row">
		    <span class="col-2"><?=__('Lock Owner:')?></span>
            <span class="col-10"><?=get_data('ownerHtml')?></span>
        </div>
		<div class="grid-row">
		    <span class="col-2"><?=__('Locking Date:')?></span>
            <span class="col-10"><?=tao_helpers_Date::displayeDate(get_data('lockDate'))?></span>
        </div>
		
        <?php if (get_data('isOwner')): ?>
		<p>
            <em><?=__('As the owner of this resource, you may release the lock')?></em>
            <button id="release" class="btn btn-warning" data-id="<?=get_data('id')?>" data-url="<?=get_data('destination')?>"><?=__('ReleaseLock')?></button>
        </p>
		<?php else : ?>
		<p><em><?=__('Please contact the owner of the resource to unlock it')?></em></p>
		<?php endif;?>
		
	</div>
    
</div>
<?php Template::inc('footer.tpl', 'tao'); ?>
