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

require_once dirname(__FILE__) .'/../install/init.php';

$parms = $argv;
array_shift($parms);

if (count($parms) != 1) {
    echo 'Usage: '.__FILE__.' CONFIG_FILE_PATH '.PHP_EOL;
    die(1);
}

$filepath = array_shift($parms);

$script = new tao_install_Setup();

/** @var common_report_Report $report */
$report = call_user_func($script, array($filepath));

echo helpers_Report::renderToCommandLine($report);

