<?php
?>
<div>
    Hi, <?= get_data('user_name') ?>

    You indicated that you have forgotten your TAO password.

    To reset your password, click the link below, or paste it into your browser.
    You will then be prompted to create a new password.
    
    <?= get_data('link') ?>
    

    If you do not wish to reset your password, just ignore this email
    and your password will remain the same.
</div>