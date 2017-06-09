<?php
/**
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
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
 *
 *
 */

$params = $argv;
array_shift($params);
$filePath = array_shift($params);

if (empty($filePath) || ($filePath !== ltrim($filePath, '-'))) {
    echo 'Usage: '.__FILE__.' [CONFIG_FILE_PATH] [OPTION]' . PHP_EOL;
    echo '   -v    | --verbose 1   verbose mode(error level)' . PHP_EOL;
    echo '   -vv   | --verbose 2   verbose mode(error & notice levels)' . PHP_EOL;
    echo '   -vvv  | --verbose 3   verbose mode(error & notice & info levels)' . PHP_EOL;
    echo '   -vvvv | --verbose 4   verbose mode(all levels)' . PHP_EOL;
    echo '   -nc   | --no-color    removing colors from the output' . PHP_EOL;
    echo 'Example:' . PHP_EOL;
    echo '   ' . __FILE__ . ' taoConfig.json -vv' . PHP_EOL;
    echo '   ' . __FILE__ . ' taoConfig.json -vvvv -nc' . PHP_EOL;
    exit(1);
}

try {
    require_once dirname(__FILE__) .'/../install/init.php';

    // Adding file path to the dependency container.
    $container->offsetSet(tao_install_Setup::CONTAINER_INDEX, array($filePath));

    $script = new tao_install_Setup();
    call_user_func($script, $container);
}
catch (Exception $e) {
    $container->offsetGet(\oat\oatbox\log\LoggerService::SERVICE_ID)
        ->getLogger()
        ->error($e->getMessage());

    if (PHP_SAPI == 'cli') {
        exit($e->getCode() == 0 ? 1 : $e->getCode());
    }
}
