<?php
use oat\tao\helpers\Template;
use oat\tao\helpers\Layout;
?>

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
