<?php

/*
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 31 Milk St # 960789 Boston, MA 02196 USA.
 *
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA;
 */

/**
 * Lightweight bootstrap for translation compilation.
 *
 * Allows taoTranslate.php compile actions to run without a full TAO installation
 * (no database, no generis.conf.php) when TAO_TRANSLATE_STANDALONE is enabled.
 * Intended for CI environments such as GitHub Actions where extensions are
 * installed via Composer.
 *
 * Optional variables that may be set before including this file:
 * - $taoTranslateRootPath: absolute path to the TAO platform root directory
 */

if (!defined('TAO_TRANSLATE_STANDALONE_MODE')) {
    define('TAO_TRANSLATE_STANDALONE_MODE', true);
}

if (PHP_SAPI === 'cli') {
    $_SERVER['HTTP_HOST'] = 'http://localhost';
}

$platformRoot = null;

if (isset($taoTranslateRootPath) && is_string($taoTranslateRootPath) && $taoTranslateRootPath !== '') {
    $platformRoot = realpath($taoTranslateRootPath);
}

if ($platformRoot === false || $platformRoot === null) {
    $platformRoot = realpath(dirname(__DIR__, 2));
}

if ($platformRoot === false) {
    fwrite(STDERR, "Unable to resolve the TAO platform root path.\n");
    exit(1);
}

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', $platformRoot . DIRECTORY_SEPARATOR);
}

if (!defined('DEFAULT_LANG')) {
    define('DEFAULT_LANG', 'en-US');
}

if (!defined('CONFIG_PATH')) {
    define('CONFIG_PATH', ROOT_PATH . 'config' . DIRECTORY_SEPARATOR);
}

if (!defined('TAO_DEFAULT_ENCODING')) {
    define('TAO_DEFAULT_ENCODING', 'UTF-8');
}

$autoloadPath = $platformRoot . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

if (!is_readable($autoloadPath)) {
    fwrite(STDERR, "Composer autoload not found at '{$autoloadPath}'.\n");
    exit(1);
}

require_once $autoloadPath;
