<link rel="stylesheet" type="text/css" href="<?= TAOBASE_WWW ?>css/report.css" media="screen"/>
<script type="text/javascript">
	$(function() {
		require(['jquery', 'tao/report'], function($, report){
			$('#fold > input[type="checkbox"]').click(function() {
				report.fold();
			});
		});
	});
	
</script>
<div class="main-container">
	<div class="ui-widget-header ui-corner-top ui-state-default">
		<?= __('Import report'); ?>
	</div>
	<div class="ui-widget-content ui-corner-bottom report">
		<? if (get_data('report')->hasChildren() === true): ?>
		<label class="tao-scope" id="fold">
			<span><?= __("Show detailed report"); ?></span>
			<input type="checkbox"/>
		</label>
		<? endif; ?>
		<?= tao_helpers_report_Rendering::render(get_data('report')); ?>
	</div>
</div>