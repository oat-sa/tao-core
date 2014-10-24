<?php
use oat\tao\helpers\Template;
?>
<link rel="stylesheet" type="text/css" href="<?= Template::css('report.css','tao') ?>" media="screen"/>
<div class="main-container">
	<div class="ui-widget-header ui-corner-top ui-state-default">
		<?php if (has_data('title')): ?>
			<?php echo $title; ?>
		<?php else: ?>
			<?php echo __('Import report'); ?>
		<?php endif; ?>
	</div>
	<div class="ui-widget-content ui-corner-bottom report">
		<?php if (get_data('report')->hasChildren() === true): ?>
		<label class="tao-scope" id="fold">
			<span><?= __("Show detailed report"); ?></span>
			<input type="checkbox"/>
		</label>
		<?php endif; ?>
		<?php echo tao_helpers_report_Rendering::render(get_data('report')); ?>
	</div>
</div>
<script type="text/javascript">
require(['jquery', 'tao/report'], function($, report){

    // Fold action (show detailed report).
    $('#fold > input[type="checkbox"]').click(function() {
        report.fold();
    });
    
    // Continue button
    $('#import-continue').on('click', function() {
        $('.tree').trigger('refresh.taotree', [{
            uri : <?=json_encode(get_data('selectNode'))?>
        }]);
    });
    
});
</script>
