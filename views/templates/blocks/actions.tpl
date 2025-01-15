<?php
use oat\tao\helpers\Layout;

$actions_list = Layout::isQuickWinsDesignEnabled() ? Layout::getSortedActionsByWeight('actions') : get_data('actions');
?>

    <?php foreach ($actions_list as $action): ?>
    <li class="action <?= get_data('action_classes')?>"
        id="<?=$action->getId()?>"
        title="<?= __($action->getName()) ?>"
        data-context="<?= $action->getContext() ?>"
        data-action="<?= $action->getBinding() ?>"
        data-multiple="<?= $action->isMultiple() ? 'true' : 'false' ?>"
        data-rights='<?= json_encode($action->getRequiredRights()) ?>' >
        <a class="li-inner" href="<?= $action->getUrl(); ?>">
            <?= Layout::renderIcon( $action->getIcon(), ' icon-magicwand'); ?>
            <?php if(Layout::isQuickWinsDesignEnabled()): ?>
                <span class="action-name"><?= __($action->getName()) ?></span>
            <?php else: ?>
                <?= __($action->getName()) ?>
            <?php endif; ?>
        </a>
    </li>
    <?php endforeach; ?>
