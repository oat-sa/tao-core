<?php
/**
 * This script aims at checking that the generis.conf.php file
 * is present at the appropriate location on the system and is
 * readable.
 * 
 * In case of the generis.conf.php file is readable at the appropriate
 * location, the script exits with code 0. Otherwise, the script
 * exits with code 128.
 * 
 * An optional argument 'p' can be given to the script in order to indicate
 * the path to the configuration folder. In case of 'p' is not given,
 * the script will search for configuration in INSTALL_PATH/config.
 * 
 * An optional argument 'v' enables verbose mode.
 * 
 * Example usage:
 * sudo -u www-data php tao/scripts/tools/IsInstalled.php
 * sudo -u www-data php tao/scripts/tools/IsInstalled.php -p ./config -v
 */
require(__DIR__ . '/../../../vendor/autoload.php');

$options = getopt('p:v::');
$path = (!isset($options['p'])) ? '' : $options['p'];
$isInstalled = \tao_install_utils_System::isTAOInstalled($path);
$exitCode = ($isInstalled) ? 0 : 128;

if (isset($options['v'])) {
    echo (($isInstalled) ? "TAO is installed.\n" : "TAO is NOT installed.\n");
}

exit($exitCode);
