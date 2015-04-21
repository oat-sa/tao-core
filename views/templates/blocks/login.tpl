<?php
use oat\tao\helpers\Layout;
?>
<div id="login-box" class="entry-point entry-point-container">
    <h1><?=Layout::getLoginMessage()?></h1>
    <?= get_data('form') ?>
    <?php if (get_data('messageServiceIsAvailable')): ?>
    <a href="<?= _url('index', 'PasswordRecovery', 'tao') ?>"> <?= __("Unable to access your account?") ?></a>
    <?php endif; ?>
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
