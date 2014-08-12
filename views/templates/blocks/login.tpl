<div id="portal-box">
    <?php if (has_data('msg')) : ?>
        <span class="loginHeader">
		    <span class="hintMsg"><?= get_data('msg') ?></span>
		</span>
    <?php endif; ?>
    <div class="loginBox">
        <? if (get_data('errorMessage')): ?>
            <div class="ui-widget ui-corner-all ui-state-error error-message">
                <?= urldecode(get_data('errorMessage')) ?>
            </div>
        <? endif ?>
        <div id="login-form">
            <?= get_data('form') ?>
        </div>
    </div>
</div>