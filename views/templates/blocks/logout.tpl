<?php
use oat\tao\helpers\Layout;
?>

<?php if(Layout::isQuickWinsDesignEnabled()): ?>
    <li data-env="user" class="li-logout">
<?php else: ?>
    <li data-env="user" class="li-logout<?php if(!empty($userLabel) && print ' sep-before')?>">
<?php endif; ?>
        <a id="logout" href="<?= get_data('logout') ?>" title="<?= __('Log Out') ?>">
            <span class="icon-logout glyph"></span>
            <span class="text hidden logout-text"><?= __("Logout"); ?></span>
        </a>
    </li>
