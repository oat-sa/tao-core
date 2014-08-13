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
<div class="feedback-error js-warning js-hide">
    <span class="icon-error"></span>
    <?=__('You must activate JavaScript in your browser to run this application.')?>
</div>
    <div class="content-wrap">

        <?php /* alpha|beta|sandbox message */
        Template::inc('blocks/version-warning.tpl', 'tao'); ?>

        <?php /* <header> + <nav> */
        Template::inc('blocks/header.tpl', 'tao'); ?>

        <div class="progress-bar"></div>

        <div id="feedback-box"></div>

        <?php /* actual content */
        $contentTemplate = Layout::getContentTemplate();
        Template::inc($contentTemplate['path'], $contentTemplate['ext']); ?>
    </div>

<footer class="dark-bar">
    <a href="http://taotesting.com" target="_blank">
        © 2013 - <?= date('Y') ?> · <?= TAO_VERSION_NAME ?> · Open Assessment Technologies S.A. · <?= __('All rights reserved.') ?>
    </a>
</footer>

</body>
</html>