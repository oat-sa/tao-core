<?php
use oat\tao\helpers\Template;
use oat\tao\helpers\Layout;
?>
<!doctype html>
<html class="no-js">
<head>
    <script>document.documentElement.className = document.documentElement.className.replace('no-js', 'js');</script>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Layout::getTitle() ?></title>
    <link rel="shortcut icon" href="<?= BASE_WWW ?>img/favicon.ico"/>

    <?= tao_helpers_Scriptloader::render() ?>
    <?= Layout::getAmdLoader() ?>

    <?php /* error handling */
    Template::inc('errors.tpl', 'tao')
    ?>
</head>

<body>
<div class="content-wrap">

    <?php if ($releaseMsgData['isUnstable'] || $releaseMsgData['isSandbox']) : ?>
        <!-- alpha|beta|sandbox message -->
        <div class="feedback-warning small release-warning">
            <span class="icon-warning"></span>
            <?= $releaseMsgData['versionType'] ?> ·
            <?php if ($releaseMsgData['isUnstable']): ?>
                <a href="http://forge.taotesting.com/projects/tao" target="_blank">
                    <?= __('Please report bugs, ideas, comments or feedback on the TAO Forge') ?>
                </a>
            <?php else: ?>
                <?= __('All data will be removed in %s', Layout::getSandboxExpiration()) ?>
            <?php endif; ?>
            <!--span title="<?= __('Remove Message') ?>" class="icon-close close-trigger"></span-->
        </div>
        <!-- /alpha|beta|sandbox message -->
    <?php endif; ?>

    <header class="dark-bar clearfix">
        <nav>
            <a href="<?= _url('entry', 'Main', 'tao') ?>" title="<?= __('TAO Home') ?>" class="lft">
                <img src="<?= TAOBASE_WWW ?>media/tao-logo.png" alt="TAO Logo" id="tao-main-logo"/>
            </a>
            <ul class="plain clearfix lft main-menu">
                <?php foreach (get_data('main-menu') as $entry): ?>
                    <li <?php if (get_data('shownExtension') === $entry->getExtension()
                            && get_data('shownStructure') === $entry->getId()): ?>class="active"<?php endif ?>>
                        <a href="<?= $entry->getUrl() ?>" title="<?= __($entry->getDescription()) ?>">
                            <?= Layout::renderMenuIcon($entry->getIcon()) ?>
                            <?= __($entry->getName()) ?></a>
                        <ul>
                            <?php foreach ($entry->getChildren() as $child): ?>
                                <li><?php echo $child->getName()?></li>
                            <?php endforeach;?>
                        </ul>
                    </li>
                <?php endforeach ?>
            </ul>
            <ul class="plain clearfix settings-menu rgt">

                <!-- check for ? and take out -->
                <!-- loop over the rest -->
                <?php /*foreach (get_data('settings-menu') as $entry): ?>
                    <li>
                        <a id="<?= $entry['id'] ?>" <?php if (isset($entry['js'])): ?> href="#" data-action="<?= $entry['js'] ?>"
                        <?php else : ?>
                            href="<?= $entry['url'] ?>"
                        <?php endif ?> title="<?= __($entry['name']) ?>">

            <div>
                <a id="logout" href="<?=_url('logout', 'Main', 'tao')?>" title="<?=__('Log Out')?>">
                    <span class="icon-logout"></span>
                </a>
            </div>
            <?php if (tao_models_classes_accessControl_AclProxy::hasAccess('properties', 'UserSettings', 'tao')): ?>
            <div class="vr">|</div>
            <div class="usersettings">
                <a id="usersettings"
                   href="<?=_url('index', 'Main', 'tao', array('structure' => 'user_settings', 'ext' => 'tao'))?>"
                   title="<?=__('My profile')?>">
                    <span class="icon-user"></span>
                    <span class="username"><?=get_data('userLabel')?></span>
                </a>
            </div>
            <?php endif ?>

                            <?php if (isset($entry['text'])): ?>
                                <?= __($entry['text']) ?>
                            <?php endif ?>

                        </a>
                    </li>
                <?php endforeach */?>


                <?php if (tao_models_classes_accessControl_AclProxy::hasAccess(null, 'UserSettings', 'tao')): ?>
                    <li data-env="user" class="separate">
                        <a id="usersettings"
                           href="<?= _url(
                               'index',
                               'Main',
                               'tao',
                               array('structure' => 'user_settings', 'ext' => 'tao')
                           ) ?>"
                           title="<?= __('My profile') ?>">
                            <span class="icon-user"></span>
                            <span class="username"><?= get_data('userLabel') ?></span>
                        </a>
                    </li>
                <?php endif ?>

                <li data-env="user">
                    <a id="logout" href="<?= _url('logout', 'Main', 'tao') ?>" title="<?= __('Log Out') ?>">
                        <span class="icon-logout"></span>
                    </a>
                </li>

            </ul>
        </nav>
    </header>


    <?php /* progress bar */
    Template::inc('blocks/progressbar.tpl', 'tao'); ?>

    <?php if (get_data('sections')): ?>

    <div id="tabs">
        <ul>
            <?php foreach(get_data('sections') as $section):?>
            <li id="tab-<?=$section['id']?>"><a id="<?=$section['id']?>" href="<?=ROOT_URL . substr($section['url'], 1) ?>"
                   title="<?=$section['name']?>"><?=__($section['name'])?></a></li>
            <?php endforeach ?>
        </ul>

            <div id="sections-aside">
                <div id="section-trees"></div>
                <div id="section-actions"></div>
            </div>
            <div id="section-meta"></div>
        </div>
    <?php endif; ?>

        <div id="feedback-box"></div>

        <?php /* actual content */
        $contentTemplate = Layout::getContentTemplate();
        Template::inc($contentTemplate['path'], $contentTemplate['ext']); ?>
    </div>
<?php
Template::inc('footer.tpl', 'tao');
Template::inc('blocks/html-close.tpl', 'tao');
?>
