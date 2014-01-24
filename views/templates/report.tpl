<link rel="stylesheet" type="text/css" href="<?= TAOBASE_WWW ?>css/report.css" media="screen"/>
<div class="main-container">
	<div class="ui-widget-header ui-corner-top ui-state-default">
		<?= __('Import report'); ?>
	</div>
	<div class="ui-widget-content ui-corner-bottom report">
		<?= tao_helpers_report_Rendering::render(get_data('report')); ?>
	</div>
</div>