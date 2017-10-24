<?php
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
