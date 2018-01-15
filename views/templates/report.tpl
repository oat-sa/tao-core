<?php
use oat\tao\helpers\Template;
?>
<link rel="stylesheet" type="text/css" href="<?= Template::css('report.css','tao') ?>" media="screen"/>
<div class="section-header flex-container-full">
    <h2>
    <?= get_data('report')->getType() == common_report_Report::TYPE_ERROR
            ? __('Error')
            : __('Success'); ?>
    </h2>
</div>
<div class="main-container flex-container-full report">
    <?php if (get_data('report')->hasChildren() === true): ?>
    <label id="fold">
        <span class="check-txt"><?php echo __("Show detailed report"); ?></span>
        <input type="checkbox"/>
        <span class="icon-checkbox"></span>
    </label>
    <?php endif; ?>
    <?php echo tao_helpers_report_Rendering::render(get_data('report')); ?>
</div>
<script type="text/javascript">
require(['jquery', 'i18n', 'layout/actions'], function($, __, actionManager){

    var $toggleDetails = $('#fold > span.check-txt');
    var $top = $('.report > .feedback-nesting-0');
    var $content = $top.children('div');

    // Fold action (show detailed report).
    $('#fold > input[type="checkbox"]').click(function() {

        if ($content.css('display') === 'none') {
            $content.css('display', 'block');
            $top.css('background-color', 'transparent');
            $top.css('border-color', 'transparent');

            $toggleDetails.text(__('Hide detailed report'));
        }
        else {
            $content.css('display', 'none');
            if ($top.hasClass('feedback-success')) {
                $top.css('border-color', '#3ea76f');
                $top.css('background-color', '#e6f4ed');
            }
            else if ($top.hasClass('feedback-warning')) {
                $top.css('border-color', '#dfbe7b');
                $top.css('background-color', '#fbf6ee');
            }
            else if ($top.hasClass('feedback-error')) {
                $top.css('border-color', '#c74155');
                $top.css('background-color', '#f8e7e9');
            }
            else {
                // info
                $top.css('border-color', '#3e7da7');
                $top.css('background-color', '#e6eef4');
            }

            $toggleDetails.text(__('Show detailed report'));
        }
    });

    // Continue button
    $('#import-continue').on('click', function() {
        <?php if (has_data('selectNode')): ?>
            actionManager.trigger('refresh', {
                uri : <?php echo json_encode(\tao_helpers_Uri::decode(get_data('selectNode'))); ?>,
            });
        <?php else : ?>
        actionManager.trigger('refresh');
        <?php endif; ?>
    });
});
</script>
