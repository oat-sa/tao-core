<?php
use oat\tao\helpers\Layout;
$mainMenu     = get_data('main-menu');
$settingsMenu = get_data('settings-menu');
?>
<nav>
    <ul class="plain clearfix lft main-menu">
        <?php if($mainMenu): ?>
            <?php foreach ($mainMenu as $entry): ?>
                <li <?php if (get_data('shownExtension') === $entry->getExtension()
                && get_data('shownStructure') === $entry->getId()): ?>class="active"<?php endif ?>>
                    <a href="<?= $entry->getUrl() ?>" title="<?= __($entry->getDescription()) ?>">
                        <?= Layout::renderIcon($entry->getIcon(), 'icon-extension') ?>
                        <?= __($entry->getName()) ?></a>
                    <?php if (count($entry->getChildren()) > 1): ?>
                        <ul class="plain menu-dropdown">
                            <?php foreach ($entry->getChildren() as $child): ?>
                                <?php if(!$child->getDisabled()) : ?>
                                <li>
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

    <ul class="plain clearfix settings-menu rgt">
        <!--li class="info-listing warning feedback-listing">
            <ul>
                <li class="info-listing warning feedback-listing">3</li>
                <li class="info-listing success feedback-listing">3</li>
                <li class="warning-listing feedback-listing">3</li>
                <li class="error-listing feedback-listing">4</li>
            </ul>
        </li-->
        <?php if($settingsMenu): ?>
            <?php foreach ($settingsMenu as $entry): ?>
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
                    <?php if (count($entry->getChildren()) > 1): ?>
                        <ul class="plain menu-dropdown">
                            <?php foreach ($entry->getChildren() as $child): ?>
                                <?php if(!$child->getDisabled()) : ?>
                                <li>
                                    <a href="<?= $entry->getUrl() ?>&section=<?= $child->getId() ?>"><?php echo $child->getName() ?></a>
                                </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php endforeach ?>
        <?php endif; ?>

        <li data-env="user" class="li-logout">
            <a id="logout" href="<?= _url('logout', 'Main', 'tao') ?>" title="<?= __('Log Out') ?>">
                <span class="icon-logout"></span>
            </a>
        </li>

    </ul>
</nav>

