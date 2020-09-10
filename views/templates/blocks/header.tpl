<?php
use oat\tao\helpers\Template;
use oat\tao\helpers\Layout;
use oat\tao\model\theme\Theme;
?>
<?php Template::inc('blocks/careers.tpl', 'tao'); ?>
<header aria-label="<?=__('Main Menu')?>" class="dark-bar clearfix">

    <?php if ((bool) get_data('hideLogo') === false): ?>
        <?=Layout::renderThemeTemplate(Theme::CONTEXT_BACKOFFICE, 'header-logo')?>
    <?php endif; ?>

    <?php /* main navigation bar */
    !common_session_SessionManager::isAnonymous()
        ? Template::inc('blocks/header-main-navi.tpl', 'tao')
        : '';
    ?>

</header>
