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
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *               2013-     (update and modification) Open Assessment Technologies SA;
 */
$root = realpath(__DIR__ . '/../../') . DIRECTORY_SEPARATOR;
define('TAO_INSTALL_PATH', $root);

// only script that may use the quick TZ fix
if(function_exists("date_default_timezone_set")){
	date_default_timezone_set('UTC');
}

require_once ($root . 'vendor/autoload.php');

if (tao_install_utils_System::isTAOInstalled()) {
    require_once ($root . '/config/generis.conf.php');
}

// Logger service initialization.
$loggerService = new \oat\oatbox\log\LoggerService();
$loggerService->addLogger(
    \oat\oatbox\log\VerboseLoggerFactory::getInstance(
        empty($argv)
            ? ['-nc', '-vv'] // For no color visualization but showing the notices.
            : $argv
    )
);

$loggerService->addLogger(
    new \oat\oatbox\log\logger\TaoLog(array(
        'appenders' => array(
            array(
                'class' => 'SingleFileAppender',
                'threshold' => common_Logger::TRACE_LEVEL,
                'file' => TAO_INSTALL_PATH . 'tao/install/log/install.log'
            )
        )
    ))
);

// Initializing the dependency container.
$container = new \Pimple\Container(
    array(
        \oat\oatbox\log\LoggerService::SERVICE_ID => $loggerService,
    )
);
