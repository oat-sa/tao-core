<?php
use oat\tao\helpers\Template;
use oat\tao\helpers\Layout;
use oat\tao\model\theme\Theme;
?>
<?php Template::inc('blocks/careers.tpl', 'tao'); ?>
<header aria-label="<?=__('Main Menu')?>" class="main-header <?= Layout::isQuickWinsDesignEnabled() ? 'red-bar' : 'dark-bar' ?> clearfix">

    <?php if ((bool) get_data('hideLogo') === false): ?>
        <?=Layout::renderThemeTemplate(Theme::CONTEXT_BACKOFFICE, 'header-logo', ['userLabel' => get_data('userLabel'), 'portalUrl' => get_data('portalUrl')])?>
    <?php endif; ?>

    <?php /* main navigation bar */
    !common_session_SessionManager::isAnonymous()
        ? Template::inc('blocks/header-main-navi.tpl', 'tao')
        : '';
    ?>

</header>
