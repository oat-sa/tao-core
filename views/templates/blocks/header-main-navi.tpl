<?php
use oat\tao\helpers\Layout;
use oat\tao\model\theme\Theme;

$mainMenu     = get_data('main-menu');
$settingsMenu = get_data('settings-menu');
$persistentMenu = get_data('persistent-menu');
$userLabel    = get_data('userLabel');
$taoAsATool   = get_data('taoAsATool');
?>
<nav>
    <ul class="plain clearfix lft main-menu">
        <?php if($mainMenu): ?>
            <?php foreach ($mainMenu as $item): ?>
                <?php $entry = $item['perspective']; ?>
                <li class="main-menu__item <?php if (get_data('shownExtension') === $entry->getExtension()
                && get_data('shownStructure') === $entry->getId()): ?>active<?php endif ?>">
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


    <div class="setting-menu-container">
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
                        <li class="<?= $className ?> setting-menu__item">
                            <a id="<?= $entry->getId() ?>" <?php
                            if (!is_null($entry->getBinding())): ?> href="#" data-action="<?= $entry->getBinding() ?>"
                            <?php else : ?>
                                href="<?= $entry->getUrl() ?>"
                            <?php endif ?> title="<?= __($entry->getName()) ?>">
                                <?php if(!Layout::isQuickWinsDesignEnabled()): ?>
                                    <?= is_null($entry->getIcon()) ? '' : Layout::renderIcon($entry->getIcon(), 'icon-extension') ?>
                                <?php endif; ?>
                                <?php if(Layout::isQuickWinsDesignEnabled()): ?>
                                    <?php if ($entry->getId() !== 'user_settings'): ?>
                                        <span> <?= $entry->getName() ?> </span>
                                    <?php endif ?>
                                <?php endif; ?>

                                <?php $description = $entry->getDescription();
                                if ($description): ?>
                                    <?= __($description) ?>
                                <?php endif ?>

                                <?php if ($entry->getId() === 'user_settings'): ?>
                                    <?php if(Layout::isQuickWinsDesignEnabled()): ?>
                                        <?= is_null($entry->getIcon()) ? '' : Layout::renderIcon($entry->getIcon(), 'icon-extension') ?>
                                    <?php endif; ?>
                                    <?php if(!Layout::isQuickWinsDesignEnabled()): ?>
                                        <span class="username"><?= get_data('userLabel') ?></span>
                                    <?php endif; ?>
                                <?php endif; ?>

                            </a>
                            <?php if (count($item['children']) > 1 || $taoAsATool): ?>
                                <ul class="plain menu-dropdown">
                                    <?php foreach ($item['children'] as $child): ?>
                                        <?php if(!$child->getDisabled()) : ?>
                                            <li<?=$child->getId() === get_data('current-section') ? ' class="active"' : '' ?>>
                                                <a href="<?= $entry->getUrl() ?>&section=<?= $child->getId() ?>"><?php echo __($child->getName()) ?></a>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <?php if ($entry->getId() === 'user_settings'): ?>
                                        <?= Layout::renderThemeTemplate(Theme::CONTEXT_BACKOFFICE, 'logout-menu-settings', ['logout' => get_data('logout')]); ?>
                                    <?php endif; ?>
                                </ul>
                            <?php elseif ($entry->getId() === 'user_settings'): ?>
                                <?php if(Layout::renderThemeTemplate(Theme::CONTEXT_BACKOFFICE, 'logout-menu-settings', ['logout' => get_data('logout')])) : ?>
                                    <ul class="plain menu-dropdown">
                                            <?= Layout::renderThemeTemplate(Theme::CONTEXT_BACKOFFICE, 'logout-menu-settings', ['logout' => get_data('logout')]); ?>
                                    </ul>
                                <?php endif; ?>
                            <?php endif; ?>
                        </li>

                    <?php endforeach ?>

                <?php else: ?>
                    <?php $displayName = !empty($userLabel) ? $userLabel : __('User'); ?>
                    <li class="infoControl user-label-outside-menu">
                        <span class="a">
                            <span class="icon-user"></span>
                            <span><?= $displayName ?></span>
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
                <?= Layout::renderThemeTemplate(Theme::CONTEXT_BACKOFFICE, 'logout', ['userLabel' => $userLabel, 'logout' => get_data('logout')]); ?>
            </ul>
        </div>

        <div class="persistent-menu rgt">
            <ul class="clearfix plain">
                <?php if($persistentMenu): ?>
                    <?php foreach ($persistentMenu as $item): ?>
                    <?php $entry = $item['perspective']; ?>
                    <?php $className = get_data('shownExtension') === $entry->getExtension() && get_data(
                    'shownStructure'
                    ) === $entry->getId()
                    ? 'active li-' . $entry->getId()
                    : 'li-' . $entry->getId();?>
                    <li class="<?= $className ?>">
                        <a id="<?= $entry->getId() ?>" <?php

                            if (!is_null($entry->getBinding())): ?> href="#" data-action="<?= $entry->getBinding() ?>"<?php
                                else : ?> href="<?= $entry->getId()!= 'taskqueue' ? $entry->getUrl() : "#" ?>"<?php
                            endif ?> title="<?= __($entry->getName()) ?>">
                            <?= is_null($entry->getIcon()) ? '' : Layout::renderIcon($entry->getIcon(), 'icon-extension') ?>
                            <?php $description = $entry->getDescription();
                            if ($description): ?>
                            <?= __($description) ?>
                            <?php endif ?>
                        </a>
                    </li>
                    <?php endforeach ?>
                <?php endif; ?>
            </ul>
        </div>
      </div>
</nav>

