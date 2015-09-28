<?php
use oat\tao\helpers\Layout;
?>
<div id="login-box" class="entry-point entry-point-container">
    <?=Layout::renderThemeTemplate('backOffice', 'login-message')?>
    <?= get_data('form') ?>
    
    <?php foreach(get_data('entryPoints') as $entrypoint): ?>
    <div>
        <a href="<?= $entrypoint->getUrl() ?>"><?= $entrypoint->getTitle() ?></a>
    </div>
    <?php endforeach;?>
</div>
<script>
    requirejs.config({
        config: {
            'controller/login': {
                'message' : {
                    'info': <?=json_encode(get_data('msg'))?>,
                    'error': <?=json_encode(urldecode(get_data('errorMessage')))?>
                }
            }
        }
    });
</script>
