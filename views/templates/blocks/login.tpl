<?php
use oat\tao\helpers\Layout;
use oat\tao\model\theme\Theme;
?>
<main id="login-box" class="entry-point entry-point-container">
    <?=Layout::renderThemeTemplate(Theme::CONTEXT_BACKOFFICE, 'login-message')?>
    <div id="login-box-inner-container"></div>

    <?php foreach(get_data('entryPoints') as $entrypoint): ?>
    <div>
        <a class="entry-point-link" href="<?= $entrypoint->getUrl() ?>" role="button"><?= $entrypoint->getTitle() ?></a>
    </div>
    <?php endforeach;?>
</main>
<?php if(get_data('show_gdpr')): ?>
<?=Layout::renderThemeTemplate(Theme::CONTEXT_BACKOFFICE, 'gdpr')?>
<?php endif; ?>
<script>
    requirejs.config({
        config: {
            'controller/login': {
                'message' : {
                    'info': <?=json_encode(get_data('msg'))?>,
                    'error': <?=json_encode(urldecode(get_data('errorMessage')))?>
                },
                'disableAutocomplete' : <?=get_data('autocompleteDisabled')?>,
                'enablePasswordReveal' : <?=get_data('passwordRevealEnabled')?>,
                'disableAutofocus': <?=get_data('autofocusDisabled')?>,
                'fieldMessages': {
                    'login': <?=json_encode(get_data('fieldMessages_password'))?>,
                    'password': <?=json_encode(get_data('fieldMessages_password'))?>
                }
            }
        }
    });
</script>
