<?php
use oat\tao\helpers\Template;
?>
<script>
<?php if(has_data('errorMessage') || has_data('message')): ?>
    require(['ui/feedback'], function(feedback){
        <?php if(has_data('errorMessage')): ?>
        feedback().error(<?= json_encode(get_data('errorMessage')) ?>);
        <?php endif; ?>

        <?php if(has_data('message')): ?>
        feedback().info(<?= json_encode(get_data('message')) ?>);
        <?php endif; ?>
    });
<?php endif; ?>
<?php if(get_data('reload')): ?>
    require(['jquery', 'layout/section', 'layout/actions'], function ($, section, actionManager) {
        var $trees;
        var currentSection = section.current().selected;
        $trees = (currentSection && currentSection.panel && currentSection.panel.length) ?
            $('.tree', currentSection.panel) : $('.tree:visible');
/*        $trees.trigger('refresh.taotree', [{
            selectNode : <?=json_encode(get_data('selectNode'))?>
        }]);
*/
        <?php if (has_data('selectNode')): ?>
            actionManager.trigger('refresh', {
                uri : <?php echo json_encode(\tao_helpers_Uri::decode(get_data('selectNode'))); ?>,
            });
        <?php else : ?>
        actionManager.trigger('refresh');
        <?php endif; ?>

    });
<?php endif;?>
</script>
