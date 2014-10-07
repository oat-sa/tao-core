<?php
use oat\tao\helpers\Template;
?>
<?php if(false !== $messages = Template::getMessages() || get_data('reload')): ?>
    <script>
        <?php if($messages): ?>
        require(['ui/feedback'], function(feedback){
            <?php foreach($messages as $type => $message): ?>
            feedback().<?=$type?>(<?= $message ?>);
            <?php endforeach; ?>
        });
        <?php endif?>
        <?php if(get_data('reload')):?>
        require(['jquery'], function ($) {
            $('.tree').trigger('refresh.taotree');
        });
        <?php endif;?>
    </script>
<?php endif; ?>
