<?php
use oat\tao\helpers\Layout;
?>
<div class="password-recovery-form entry-point entry-point-container">
    <?php if (!get_data('recipientMail')): ?>
    <h1><?= __("Forgot your password?") ?></h1>
    <?= get_data('form') ?>
    <?php else: ?>
    <p>
        <?= __("A message with further instructions has been sent to your email address") ?> : <?= get_data('recipientMail') ?>
    </p>
    <a href="<?= _url('login', 'Main', 'tao') ?>"> <?= __("Return to sign in page") ?></a>
    <?php endif ?>
</div>
<script>
    requirejs.config({
        config: {
            'tao/controller/passwordRecovery': {
                'message' : {
                    'info': <?=json_encode(get_data('msg'))?>,
                    'error': <?=json_encode(urldecode(get_data('errorMessage')))?>
                }
            }
        }
    });
</script>
