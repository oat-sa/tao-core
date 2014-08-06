<?php
use oat\tao\helpers\Template;

$releaseMsgData = get_data('releaseMsgData');

Template::inc('blocks/layout-header.tpl', 'tao')
?>
<body>
<div class="content-wrap">

    <?php ($releaseMsgData['isUnstable'] || $releaseMsgData['isSandbox'])
        ? Template::inc('blocks/version-warning.tpl', 'tao')
        : '';
        /* alpha|beta|sandbox message */
    ?>

    <?php !common_session_SessionManager::isAnonymous()
        ? Template::inc('blocks/main-navi.tpl', 'tao')
        : '';
        /* main navigation bar */
    ?>

    <div class="loading-bar"></div>
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
            <div class="clearfix"></div>
            <div id="section-meta"></div>
        </div>
    <?php endif; ?>

</div>
<!-- /content-wrap -->
<?php Template::inc('layout_footer.tpl', 'tao') ?>
</body>
</html>