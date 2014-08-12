<?php
use oat\tao\helpers\Template;

/* <html><head> */
Template::inc('blocks/html-open.tpl', 'tao');
Template::inc('blocks/head.tpl', 'tao');
?>
<body>
<?= Template::inc('blocks/js-warning.tpl', 'tao'); ?>
    <div class="content-wrap">

        <?php /* alpha|beta|sandbox message */
        $releaseMsgData = get_data('releaseMsgData');

        ($releaseMsgData['is-unstable'] || $releaseMsgData['is-sandbox'])
            ? Template::inc('blocks/version-warning.tpl', 'tao')
            : '';
        ?>

        <?php /* <header> + <nav> */
        Template::inc('blocks/header.tpl', 'tao'); ?>


        <?php /* progress bar */
        Template::inc('blocks/progressbar.tpl', 'tao'); ?>

        <?php if (get_data('sections')): ?>

            <div id="tabs" class="grid-box">
                <ul class="col-12">
                    <?php foreach (get_data('sections') as $section): ?>
                        <li><a id="<?= $section['id'] ?>" href="<?= ROOT_URL . substr($section['url'], 1) ?>"
                               title="<?= $section['name'] ?>"><?= __($section['name']) ?></a></li>
                    <?php endforeach ?>
                </ul>

                <div class="panels grid-box">
                    <div id="sections-aside" class="col-2">
                        <div id="section-trees"></div>
                        <div id="section-actions"></div>
                    </div>
                    <div id="section-meta" class="col-10"></div>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php
Template::inc('blocks/footer.tpl', 'tao');

Template::inc('blocks/html-close.tpl', 'tao');