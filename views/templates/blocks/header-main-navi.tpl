<?php
use oat\tao\helpers\Layout;
$mainMenu     = get_data('main-menu');
$settingsMenu = get_data('settings-menu');
$userLabel    = get_data('userLabel');
?>
<nav>
    <ul class="plain clearfix lft main-menu">
        <?php if($mainMenu): ?>
            <?php foreach ($mainMenu as $item): ?>
                <?php $entry = $item['perspective']; ?>
                <li <?php if (get_data('shownExtension') === $entry->getExtension()
                && get_data('shownStructure') === $entry->getId()): ?>class="active"<?php endif ?>>
                    <a href="<?= $entry->getUrl() ?>" title="<?= __($entry->getDescription()) ?>">
                        <?= Layout::renderIcon($entry->getIcon(), 'icon-extension') ?>
                        <?= __($entry->getName()) ?></a>
                    <?php if (count($item['children']) > 1): ?>
                        <ul class="plain menu-dropdown">
                            <?php foreach ($item['children'] as $child): ?>
                                <?php if(!$child->getDisabled()) : ?>
                                <li<?=$child->getId() === get_data('current-section') ? ' class="active"' : '' ?>>
                                    <a href="<?= $entry->getUrl() ?>&section=<?= $child->getId() ?>"><?php echo $child->getName() ?></a>
                                </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php endforeach ?>
        <?php endif; ?>
    </ul>

    <div class="settings-menu rgt">
        <span class="reduced-menu-trigger">
            <span class="icon-mobile-menu glyph"></span>
            <?=__('More')?>
        </span>
        <ul class="clearfix plain">
            <?php if($settingsMenu): ?>
                <?php foreach ($settingsMenu as $item): ?>
                    <?php $entry = $item['perspective']; ?>
                    <?php $className = get_data('shownExtension') === $entry->getExtension() && get_data(
                        'shownStructure'
                    ) === $entry->getId()
                        ? 'active li-' . $entry->getId()
                        : 'li-' . $entry->getId();?>
                    <li class="<?= $className ?>">
                        <a id="<?= $entry->getId() ?>" <?php
                        if (!is_null($entry->getBinding())): ?> href="#" data-action="<?= $entry->getBinding() ?>"
                        <?php else : ?>
                            href="<?= $entry->getUrl() ?>"
                        <?php endif ?> title="<?= __($entry->getName()) ?>">

                            <?= is_null($entry->getIcon()) ? '' : Layout::renderIcon($entry->getIcon(), 'icon-extension') ?>

                            <?php $description = $entry->getDescription();
                            if ($description): ?>
                                <?= __($description) ?>
                            <?php endif ?>

                            <?php if ($entry->getId() === 'user_settings'): ?>

                                <span class="username"><?= get_data('userLabel') ?></span>
                            <?php endif; ?>

                        </a>
                        <?php if (count($item['children']) > 1): ?>
                            <ul class="plain menu-dropdown">
                                <?php foreach ($item['children'] as $child): ?>
                                    <?php if(!$child->getDisabled()) : ?>
                                        <li<?=$child->getId() === get_data('current-section') ? ' class="active"' : '' ?>>
                                            <a href="<?= $entry->getUrl() ?>&section=<?= $child->getId() ?>"><?php echo $child->getName() ?></a>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </li>

                <?php endforeach ?>

            <?php elseif(!empty($userLabel)): ?>

                <li class="infoControl user-label-outside-menu">
                    <span class="a">
                        <span class="icon-user"></span>
                        <span><?=$userLabel?></span>
                    </span>
                </li>
            <?php endif; ?>

            <?php if(has_data('unread-notification')): ?>
                <li data-env="user" class="li-logout">
                    <a id="logout" href="<?= get_data('notification-url') ?>" title="<?= __('Messages') ?>">
                        <span class="icon-email glyph"></span>
                        <sup class="notification-count"><?= get_data('unread-notification') ?></sup>
                    </a>
                </li>
            <?php endif; ?>
            <li data-env="user" class="li-logout<?php if(!empty($userLabel) && print ' sep-before')?>">
                <a id="logout" href="<?= get_data('logout') ?>" title="<?= __('Log Out') ?>">
                    <span class="icon-logout glyph"></span>
                    <span class="text hidden logout-text"><?= __("Logout"); ?></span>
                </a>
            </li>
        </ul>
    </div>
</nav>

