<?php
/**
 * Created by PhpStorm.
 * User: ksasim
 * Date: 29.8.15
 * Time: 17.18
 */

$loginFormSettings = array(
    'elements' => array()
);

$ext = \common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
$ext->setConfig('loginForm', $loginFormSettings);
