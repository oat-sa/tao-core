<?php
use oat\tao\helpers\Template;
?><!doctype html>
<html class="no-js tao-scope" lang="">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= PRODUCT_NAME ?> <?= TAO_VERSION ?></title>
    <link rel="shortcut icon" href="<?= BASE_WWW ?>img/favicon.ico" type="image/x-icon"/>

    <?= tao_helpers_Scriptloader::render() ?>

    <?php if(tao_helpers_Mode::is('production')): ?>
        <script id="amd-loader" src="<?= Template::js('main.min.js', 'tao') ?>" data-config="<?= get_data('client_config_url') ?>"></script>
    <?php else: ?>
        <script id="amd-loader" src="<?= Template::js('lib/require.js', 'tao') ?>" data-main="<?= TAOBASE_WWW ?>js/main" data-config="<?= get_data('client_config_url') ?>"></script>
    <?php endif ?>

    <!-- Error Handling -->
    <?php
    Template::inc('errors.tpl', 'tao')
    ?>
</head>
<body>
