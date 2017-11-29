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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */

/**
 * This script aims at checking that:
 * 
 * - the /config/generis.conf.php file is readable.
 * - the /config/generis/installation.conf.php is readable.
 * - all the extensions referenced by the installation.conf.php file
 *   are available on the system with the appropriate version.
 * 
 * In case of all the statement above are respected, the script exits
 * with code 0. Otherwise, the script exits with code 128.
 * 
 * An optional argument 'p' can be given to the script in order to indicate
 * the path to the configuration folder. In case of 'p' is not given,
 * the script will search for configuration in INSTALL_PATH/config.
 * 
 * An optional argument 'v' enables verbose mode.
 * 
 * Example usage:
 * sudo -u www-data php tao/scripts/tools/IsUpToDate.php
 * sudo -u www-data php tao/scripts/tools/IsUpToDate.php -p ./config -v
 */
require(__DIR__ . '/../../../vendor/autoload.php');

$options = getopt('p:v');
$path = (!isset($options['p'])) ? '' : $options['p'];
$isUpToDate = \tao_install_utils_System::isTAOUpToDate($path);
$exitCode = ($isUpToDate) ? 0 : 128;

if (isset($options['v'])) {
    echo (($isUpToDate) ? "TAO is up to date.\n" : "TAO is NOT up to date.\n");
}

exit($exitCode);
