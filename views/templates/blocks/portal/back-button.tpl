<?php
use oat\tao\helpers\Template;
if (get_data('userLabel')): ?>
    <a href="https://portal.docker.localhost" title="<?= __("Back to Portal"); ?>" class="lft portal-back">
        <span class="icon-untab glyph"></span>
    </a>
<?php else: ?>
    <?php Template::inc('blocks/header-logo.tpl'); ?>
<?php endif; ?>