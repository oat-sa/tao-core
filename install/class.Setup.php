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
 * Copyright (c) 2014-2018 (original work) Open Assessment Technologies SA;
 *
 *
 */


use oat\oatbox\action\Action;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\log\LoggerService;
use oat\oatbox\log\logger\TaoLog;

class tao_install_Setup implements Action
{
    // Adding container and logger.
    use \oat\oatbox\log\ContainerLoggerTrait;

    /**
     * Setup related dependencies will be reached under this offset.
     */
    const CONTAINER_INDEX = 'taoInstallSetup';

    /**
     * The setup json content offset in the container.
     */
    const SETUP_JSON_CONTENT_OFFSET = 'setupJsonContentOffset';

    /**
     * @param mixed $params The setup params.
     *
     * @throws ErrorException When a module is missing or other kind of general error.
     * @throws common_Exception When the presented config file does not exist
     * @throws common_exception_Error
     * @throws common_ext_ExtensionException When a presented parameter is invalid or malformed.
     * @throws InvalidArgumentException
     * @throws tao_install_utils_Exception
     */
    public function __invoke($params)
    {
        // Using the container if it's necessary with automatic dependency returning.
        $params = $this->initContainer($params, static::CONTAINER_INDEX);

        $this->logNotice('Installing TAO...');

        if ($this->getContainer() !== null && $this->getContainer()->offsetExists(static::SETUP_JSON_CONTENT_OFFSET)) {
            $parameters = json_decode($this->getContainer()->offsetGet(static::SETUP_JSON_CONTENT_OFFSET), true);
            if (is_null($parameters)) {
                throw new InvalidArgumentException('Your Setup JSON seed is malformed');
            }
        }
        else {
            if (!isset($params[0])) {
                throw new InvalidArgumentException('You should provide a file path');
            }

            $filePath = $params[0];

            if (!file_exists($filePath)) {
                throw new \ErrorException('Unable to find ' . $filePath);
            }

            $info = pathinfo($filePath);

            switch ($info['extension']) {
                case 'json':
                    $parameters = json_decode(file_get_contents($filePath), true);
                    if (is_null($parameters)) {
                        throw new InvalidArgumentException('Your JSON file is malformed');
                    }
                    break;
                case 'yml':
                    if (extension_loaded('yaml')) {
                        $parameters = \yaml_parse_file($filePath);
                        if ($parameters === false) {
                            throw new InvalidArgumentException('Your YAML file is malformed');
                        }
                    } else {
                        throw new ErrorException('Extension yaml should be installed');
                    }
                    break;
                default:
                    throw new InvalidArgumentException('Please provide a JSON or YAML file');
            }
        }

        /** @var LoggerService $loggerService */
        $loggerService = $this->getContainer()->offsetGet(LoggerService::SERVICE_ID);
        $loggerService->addLogger(
            new TaoLog(array(
                'appenders' => array(
                    array(
                        'class' => 'SingleFileAppender',
                        'threshold' => common_Logger::TRACE_LEVEL,
                        'file' => TAO_INSTALL_PATH . 'tao/install/log/install.log'
                    )
                )
            ))
        );

        $options = array (
            "db_driver"	=>			"mysql"
            , "db_host"	=>			"localhost"
            , "db_name"	=>			null
            , "db_pass"	=>			""
            , "db_user"	=>			""
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
            , "instance_name" =>	null
            , "extensions" =>		null
            , 'timezone'   =>      date_default_timezone_get()
            , 'extra_persistences' => []
        );

        $persistences = $parameters['configuration']['generis']['persistences'];

        if(!isset($parameters['configuration'])){
            throw new InvalidArgumentException('Your config should have a \'configuration\' key');
        }

        if(!isset($parameters['configuration']['generis'])){
            throw new InvalidArgumentException('Your config should have a \'generis\' key under \'configuration\'');
        }

        if(!isset($persistences)){
            throw new InvalidArgumentException('Your config should have a \'persistence\' key under \'generis\'');
        }

        if(!isset($persistences['default'])){
            throw new InvalidArgumentException('Your config should have a \'default\' key under \'persistences\'');
        }

        $persistence = $persistences['default'];

        if(isset($persistence['connection'])){
            if(isset($persistence['connection']['wrapperClass']) && $persistence['connection']['wrapperClass'] == '\\Doctrine\\DBAL\\Connections\\MasterSlaveConnection'){
                $options['db_driver'] = $persistence['connection']['driver'];
                $options['db_host'] = $persistence['connection']['master']['host'];
                $options['db_name'] = $persistence['connection']['master']['dbname'];
                if(isset($persistence['connection']['master']['user'])){
                    $options['db_user'] = $persistence['connection']['master']['user'];
                }
                if(isset($persistence['connection']['master']['password'])){
                    $options['db_pass'] = $persistence['connection']['master']['password'];
                }
            } else {
                $options['db_driver'] = $persistence['connection']['driver'];
                $options['db_host'] = $persistence['connection']['host'];
                $options['db_name'] = $persistence['connection']['dbname'];
                if(isset($persistence['connection']['user'])){
                    $options['db_user'] = $persistence['connection']['user'];
                }
                if(isset($persistence['connection']['password'])){
                    $options['db_pass'] = $persistence['connection']['password'];
                }
            }
        } else {
            $options['db_driver'] = $persistence['driver'];
            $options['db_host'] = $persistence['host'];
            $options['db_name'] = $persistence['dbname'];
            if(isset($persistence['user'])){
                $options['db_user'] = $persistence['user'];
            }
            if(isset($persistence['password'])){
                $options['db_pass'] = $persistence['password'];
            }
        }

        if(!isset($parameters['configuration']['global'])){
            throw new InvalidArgumentException('Your config should have a \'global\' key under \'configuration\'');
        }

        $global = $parameters['configuration']['global'];
        $options['module_namespace'] = $global['namespace'];
        $options['instance_name'] = $global['instance_name'];
        $options['module_url'] = $global['url'];
        $options['module_lang'] = $global['lang'];
        $options['module_mode'] = $global['mode'];
        $options['timezone'] = $global['timezone'];
        $options['import_local'] = (isset($global['import_data']) && $global['import_data'] === true);

        $rootDir = dir(dirname(__FILE__) . '/../../');
        $options['root_path'] = isset($global['root_path'])
            ? $global['root_path']
            : realpath($rootDir->path) . DIRECTORY_SEPARATOR;

        $options['file_path'] = isset($global['file_path'])
            ? $global['file_path']
            : $options['root_path'] . 'data' . DIRECTORY_SEPARATOR;

        if(isset($global['session_name'])){
            $options['session_name'] = $global['session_name'];
        }

        if(isset($global['anonymous_lang'])){
            $options['anonymous_lang'] = $global['anonymous_lang'];
        }

        //get extensions to install
        if(isset($parameters['extensions'])){
            $options['extensions'] = $parameters['extensions'];
        }

        if(!isset($parameters['super-user'])){
            throw new InvalidArgumentException('Your config should have a \'global\' key under \'generis\'');
        }

        $superUser = $parameters['super-user'];
        $options['user_login'] = $superUser['login'];
        $options['user_pass1'] = $superUser['password'];
        if(isset($parameters['lastname'])){
            $options['user_lastname'] = $parameters['lastname'];
        }
        if(isset($parameters['firstname'])){
            $options['user_firstname'] = $parameters['firstname'];
        }
        if(isset($parameters['email'])){
            $options['user_email'] = $parameters['email'];
        }


        $installOptions = array(
            'root_path' 	=> $options['root_path'],
            'install_path'	=> $options['root_path'].'tao/install/',
        );

        if (isset($global['installation_config_path'])) {
            $installOptions['installation_config_path'] = $global['installation_config_path'];
        }

        // run the actual install
        if ($this->getContainer() instanceof \Pimple\Container) {
            $this->getContainer()->offsetSet(\tao_install_Installator::CONTAINER_INDEX, $installOptions);
            $installator = new \tao_install_Installator($this->getContainer());
        }
        else {
            $installator = new \tao_install_Installator($installOptions);
        }

        $serviceManager = $installator->getServiceManager();

        foreach($parameters['configuration'] as $extension => $configs){
            foreach($configs as $key => $config){
                if(isset($config['type']) && $config['type'] === 'configurableService'){
                    $className = $config['class'];
                    $params = $config['options'];
                    if (is_a($className, \oat\oatbox\service\ConfigurableService::class, true)) {
                        $service = new $className($params);
                        $serviceManager->register($extension.'/'.$key, $service);
                    } else{
                        $this->logWarning('The class : ' . $className . ' can not be set as a Configurable Service');
                        $this->logWarning('Make sure your configuration is correct and all required libraries are installed');
                    }
                }
            }
        }

        // mod rewrite cannot be detected in CLI Mode.
        $installator->escapeCheck('custom_tao_ModRewrite');

        // configure persistences
        $options['extra_persistences'] = $persistences;


        $installator->install($options);

        /** @var common_ext_ExtensionsManager $extensionManager */
        $extensionManager = $serviceManager->get(common_ext_ExtensionsManager::SERVICE_ID);
        foreach($parameters['configuration'] as $ext => $configs) {
            foreach($configs as $key => $config) {
                if(! (isset($config['type']) && $config['type'] === 'configurableService')) {
                    if (! is_null($extensionManager->getInstalledVersion($ext))) {
                        $extension = $extensionManager->getExtensionById($ext);
                        if (! $extension->hasConfig($key) || ! $extension->getConfig($key) instanceof ConfigurableService) {
                            if(! $extension->setConfig($key, $config)){
                                throw new ErrorException('Your config ' . $ext . '/' . $key . ' cannot be set');
                            }
                        }
                    }
                }
            }
        }

        // execute post install scripts
        if(isset($parameters['postInstall'])){
            foreach($parameters['postInstall'] as $script){
                if (isset($script['class']) && is_a($script['class'], Action::class, true)) {
                    $object = new $script['class']();
                    if(is_a($object, ServiceLocatorAwareInterface::class)){
                        $object->setServiceLocator($serviceManager);
                    }
                    $params = (isset($script['params']) && is_array($script['params'])) ? $script['params'] : [];
                    call_user_func($object, $params);
                }
            }
        }

        $this->logNotice('Installation completed!');
    }
}
