<?php
use oat\tao\helpers\Template;
if (get_data('portalUrl')): ?>
    <a href="<?= get_data('portalUrl'); ?>" title="<?= __("Back to Portal"); ?>" class="lft portal-back">
        <span class="icon-back-button glyph"></span>
    </a>
<?php else: ?>
    <?php Template::inc('blocks/header-logo.tpl'); ?>
<?php endif; ?>
