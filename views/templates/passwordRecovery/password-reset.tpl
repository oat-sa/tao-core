<?php
use oat\tao\helpers\Layout;
?>
<div class="password-recovery-form entry-point entry-point-container">
    <?php if (!get_data('passwordChanged')): ?>
    <h1><?= __("ChangePassword") ?></h1>
    <?= get_data('form') ?>
    <?php else: ?>
    <p><?= __("Password successfully changed.") ?></p>
    <a href="<?= _url('login', 'Main', 'tao') ?>"> <?= __("Return to sign in page") ?></a>
    <?php endif ?>
</div>
