<?php
use oat\tao\helpers\Template;
if (get_data('userLabel')): ?>
    <a href="<?= get_data('portalUrl'); ?>" title="<?= __("Back to Portal"); ?>" class="lft portal-back">
        <span class="icon-back-button glyph"></span>
    </a>
    <span class="lft header-title"><?= __("Content bank") ?></span>
<?php else: ?>
    <?php Template::inc('blocks/header-logo.tpl'); ?>
<?php endif; ?>
