<?php
use oat\tao\helpers\Template;

/* <html><head> */
Template::inc('blocks/html-open.tpl', 'tao');
Template::inc('blocks/head.tpl', 'tao');
?>
<body>
<div class="content-wrap">

    <?php /* alpha|beta|sandbox message */
    $releaseMsgData = get_data('releaseMsgData');
    ($releaseMsgData['isUnstable'] || $releaseMsgData['isSandbox'])
        ? Template::inc('blocks/version-warning.tpl', 'tao')
        : '';
    ?>

    <?php /* main navigation bar */
    !common_session_SessionManager::isAnonymous()
        ? Template::inc('blocks/main-navi.tpl', 'tao')
        : '';
    ?>

    <?php /* progress bar */
    Template::inc('blocks/progressbar.tpl', 'tao'); ?>

    <?php if (get_data('sections')): ?>

        <div id="tabs">
            <ul>
                <?php foreach (get_data('sections') as $section): ?>
                    <li><a id="<?= $section['id'] ?>" href="<?= ROOT_URL . substr($section['url'], 1) ?>"
                           title="<?= $section['name'] ?>"><?= __($section['name']) ?></a></li>
                <?php endforeach ?>
            </ul>

            <div id="sections-aside">
                <div id="section-trees"></div>
                <div id="section-actions"></div>
            </div>
            <div id="section-meta"></div>
        </div>
    <?php endif; ?>

</div>
<!-- /content-wrap -->
<?php
Template::inc('footer.tpl', 'tao');
Template::inc('blocks/html-close.tpl', 'tao');
?>