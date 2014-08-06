<?php
use oat\tao\helpers\Template;
use oat\tao\helpers\Layout;

$releaseMsgData = get_data('releaseMsgData');
?>

        <div class="feedback-warning small version-warning">
            <span class="icon-warning"></span>
            <?= $releaseMsgData['versionType'] ?> ·
            <?php if ($releaseMsgData['isUnstable']): ?>
                <a href="<?= $releaseMsgData['logo-link'] ?>" target="_blank">
                    <?= __('Please report bugs, ideas, comments or feedback on the TAO Forge') ?>
                </a>
            <?php else: ?>
                <?= __('All data will be removed in %s', Layout::getSandboxExpiration()) ?>
            <?php endif; ?>
            <span title="<?= __('Remove Message') ?>" class="icon-close close-trigger"></span>
        </div>