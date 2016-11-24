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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\tao\scripts;

use oat\oatbox\action\Action;
use common_report_Report as Report;

class taoSetup implements Action
{
    public function __invoke($params)
    {

        // TODO : Take filename from parameter
        $filename = __DIR__ . '/sample/config.json';
//        $filename = __DIR__ . '/sample/config.yml';
        if (!file_exists($filename)) {
            throw new \Exception('Unable to find '. $filename);
        }


        //TODO : Take format from parameter
        $format = 'json';
//        $format = 'yml';

        switch($format){
            case 'json':
                $parameters = json_decode(file_get_contents($filename), true);
                break;
            case 'yml':
                if(extension_loaded('yaml')){
                    $parameters = \yaml_parse_file($filename);
                } else {
                    return Report::createFailure('Extension yaml should be installed');
                }
                break;
            default:
                return Report::createFailure('Please provide a format');
                break;
        }

        $options = array (
        "db_driver"	=>			"mysql"
        , "db_host"	=>			"localhost"
        , "db_name"	=>			null
        , "db_pass"	=>			""
        , "db_user"	=>			"tao"
        , "install_sent"	=>	"1"
        , "module_host"	=>		"tao.local"
        , "module_lang"	=>		"en-US"
        , "module_mode"	=>		"debug"
        , "module_name"	=>		"mytao"
        , "module_namespace" =>	""
        , "module_url"	=>		""
        , "submit"	=>			"Install"
        , "user_email"	=>		""
        , "user_firstname"	=>	""
        , "user_lastname"	=>	""
        , "user_login"	=>		""
        , "user_pass"	=>		""
        , "import_local" => 	true
        , "instance_name" =>	null
        , "extensions" =>		null
        , 'timezone'   =>      date_default_timezone_get()
        );



        if(!isset($parameters['configuration'])){
            return Report::createFailure('Your config should have a \'configuration\' key');
        }

        if(!isset($parameters['configuration']['generis'])){
            return Report::createFailure('Your config should have a \'generis\' key under \'configuration\'');
        }

        if(!isset($parameters['configuration']['generis']['persistences'])){
            return Report::createFailure('Your config should have a \'persistence\' key under \'generis\'');
        }

        foreach($parameters['configuration']['generis']['persistences'] as $key => $persistence){
            if($key === 'default'){
                $options['db_driver'] = $persistence['driver'];
                $options['db_host'] = $persistence['host'];
                $options['db_name'] = $persistence['dbname'];
                $options['db_user'] = $persistence['user'];
                $options['db_pass'] = $persistence['password'];
            }
        }

        if(!isset($parameters['configuration']['generis']['global'])){
            return Report::createFailure('Your config should have a \'global\' key under \'generis\'');
        }

        $global = $parameters['configuration']['generis']['global'];
        $options['module_namespace'] = $global['namespace'];
        $options['instance_name'] = $global['instance_name'];
        $options['root_path'] = $global['root_path'];
        $options['file_path'] = $global['file_path'];
        $options['module_url'] = $global['url'];
        $options['module_lang'] = $global['lang'];
        $options['module_mode'] = $global['mode'];
        $options['timezone'] = $global['timezone'];
        if(isset($global['session_name'])){
            $options['session_name'] = $global['session_name'];
        }

        //get extensions to install
        if(isset($parameters['extensions'])){
            $options['extensions'] = $parameters['extensions'];
        }

        if(!isset($parameters['super-user'])){
            return Report::createFailure('Your config should have a \'global\' key under \'generis\'');
        }

        $superUser = $parameters['super-user'];
        $options['user_login'] = $superUser['login'];
        $options['user_lastname'] = $superUser['lastname'];
        $options['user_firstname'] = $superUser['firstname'];
        $options['user_email'] = $superUser['email'];
        $options['user_pass1'] = $superUser['password'];


        // FlySystem config


        // run the actual install
        $installator = new \tao_install_Installator (array(
            'root_path' 	=> $options['root_path'],
            'install_path'	=> $options['root_path'].'tao/install/'
        ));

        // mod rewrite cannot be detected in CLI Mode.
        $installator->escapeCheck('custom_tao_ModRewrite');
        $installator->install($options);


        // Can be PostInstalled
        // DeliveryExecution config
        // serviceState config
        // session config
        // uriProvider config
        // authentication config


        // execute post install scripts
        if(isset($parameters['postInstall'])){
            foreach($parameters['postInstall'] as $script){
                $object = new $script['class']();
                if($object instanceof Action){
                    call_user_func($object, $script['params']);
                }
            }
        }


        return Report::createSuccess(print_r($options,true));
    }
}