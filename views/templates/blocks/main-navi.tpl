<?php
use oat\tao\helpers\Template;
use oat\tao\helpers\Layout;

$releaseMsgData = get_data('releaseMsgData');
?>

  <header class="dark-bar clearfix">
        <nav>
            <a href="<?= $releaseMsgData['logo-link'] ?>" title="<?= __($releaseMsgData['logo-title']) ?>" class="lft" target="_blank">
                <img src="<?= TAOBASE_WWW ?>media/<?= $releaseMsgData['logo'] ?>" alt="TAO Logo" id="tao-main-logo"/>
            </a>
            <ul class="plain clearfix lft main-menu">
                <?php foreach (get_data('main-menu') as $entry): ?>
                    <li <?php if (get_data('shownExtension') === $entry->getExtension()
                            && get_data('shownStructure') === $entry->getId()): ?>class="active"<?php endif ?>>
                        <a href="<?= $entry->getUrl() ?>" title="<?= __($entry->getDescription()) ?>">
                            <?= Layout::renderMenuIcon($entry->getIcon()) ?>
                            <?= __($entry->getName()) ?></a>
                        <?php if(count($entry->getChildren()) > 1): ?>
                        <ul class="plain">
                            <?php foreach ($entry->getChildren() as $child): ?>
                                <li>
                                    <a href="<?= ROOT_URL . ltrim($child->getUrl(), '/') ?>"><?php echo $child->getName()?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach ?>
            </ul>
            <ul class="plain clearfix settings-menu rgt">
                <?php foreach (get_data('settings-menu') as $entry): ?>
                    <?php $className = get_data('shownExtension') === $entry->getExtension() && get_data('shownStructure') === $entry->getId()
                          ? 'active li-' . $entry->getId()
                          : 'li-' . $entry->getId();?>
                    <li class="<?=$className?>">
                        <a id="<?= $entry->getId() ?>" <?php $js = $entry->getJs(); if (!empty($js)): ?> href="#" data-action="<?= $entry->getJs() ?>"
                        <?php else : ?>
                            href="<?= $child->getUrl() ?>"
                        <?php endif ?> title="<?= __($entry->getName()) ?>">

                            <?= is_null($entry->getIcon()) ? '' : Layout::renderMenuIcon($entry->getIcon()) ?>

                            <?php $description = $entry -> getDescription(); if ($description): ?>
                                <?= __($description) ?>
                            <?php endif ?>

                            <?php if($entry->getId() === 'user_settings'): ?>

                                <span class="username"><?= get_data('userLabel') ?></span>
                            <?php endif;?>

                        </a>
                        <?php if(count($entry->getChildren()) > 1): ?>
                            <ul class="plain">
                                <?php foreach ($entry->getChildren() as $child): ?>
                                    <li>
                                        <a href="<?= ROOT_URL . ltrim($child->getUrl(), '/') ?>"><?php echo $child->getName()?></a>
                                    </li>
                                <?php endforeach;?>
                            </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach ?>

                <li data-env="user" class="li-logout">
                    <a id="logout" href="<?= _url('logout', 'Main', 'tao') ?>" title="<?= __('Log Out') ?>">
                        <span class="icon-logout"></span>
                    </a>
                </li>

            </ul>
        </nav>
    </header>