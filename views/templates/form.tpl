<?php
use oat\tao\helpers\Template;

Template::inc('form_context.tpl', 'tao')
?>
    <div class="main-container" data-tpl="tao/form.tpl">
        <h2><?=get_data('formTitle')?></h2>
        <div class="form-content">
            <?=get_data('myForm')?>
        </div>
    </div>
    <div class="data-container-wrapper"></div>

<?php if(has_data('errorMessage')):?>
    <script>
        require(['ui/feedback'], function(feedback){
            feedback().error(<?=get_data('message')?>);
        });
    </script>
<?php endif ?>

<?php Template::inc('footer.tpl', 'tao'); ?>