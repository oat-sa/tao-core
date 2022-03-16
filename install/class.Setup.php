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
 * Copyright (c) 2014-2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

use Pimple\Container;
use oat\oatbox\action\Action;
use oat\oatbox\log\LoggerService;
use oat\oatbox\log\logger\TaoLog;
use oat\oatbox\service\ServiceManager;
use oat\oatbox\log\ContainerLoggerTrait;
use oat\oatbox\reporting\ReportInterface;
use oat\oatbox\service\ConfigurableService;
use oat\generis\persistence\PersistenceManager;
use oat\tao\model\service\InjectionAwareService;
use Doctrine\DBAL\Connections\MasterSlaveConnection;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

class tao_install_Setup implements Action
{
    // Adding container and logger
    use ContainerLoggerTrait;

    /** Setup related dependencies will be reached under this offset */
    public const CONTAINER_INDEX = 'taoInstallSetup';

    /** The setup json content offset in the container */
    public const SETUP_JSON_CONTENT_OFFSET = 'setupJsonContentOffset';

    /**
     * @param Container|array $params
     *
     * @throws ErrorException When a module is missing or other kind of general error
     * @throws common_Exception When the presented config file does not exist
     * @throws common_exception_Error
     * @throws common_ext_ExtensionException When a presented parameter is invalid or malformed
     * @throws InvalidArgumentException
     * @throws tao_install_utils_Exception
     * @throws ReflectionException
     */
    public function __invoke($params)
    {
        // Using the container if it's necessary with automatic dependency returning
        $params = $this->initContainer($params, static::CONTAINER_INDEX);

        $this->logNotice('Installing TAO...');

        if (
            $this->getContainer() !== null
            && $this->getContainer()->offsetExists(static::SETUP_JSON_CONTENT_OFFSET)
        ) {
            $parameters = json_decode(
                $this->getContainer()->offsetGet(static::SETUP_JSON_CONTENT_OFFSET),
                true
            );

            if ($parameters === null) {
                throw new InvalidArgumentException('Your Setup JSON seed is malformed');
            }
        } else {
            if (!isset($params[0])) {
                throw new InvalidArgumentException('You should provide a file path');
            }

            $filePath = $params[0];

            if (!file_exists($filePath)) {
                throw new ErrorException('Unable to find ' . $filePath);
            }

            $info = pathinfo($filePath);

            switch ($info['extension']) {
                case 'json':
                    $parameters = json_decode(file_get_contents($filePath), true);

                    if ($parameters === null) {
                        throw new InvalidArgumentException('Your JSON file is malformed');
                    }

                    break;
                case 'yml':
                    if (!extension_loaded('yaml')) {
                        throw new ErrorException('Extension yaml should be installed');
                    }

                    $parameters = yaml_parse_file($filePath);

                    if ($parameters === false) {
                        throw new InvalidArgumentException('Your YAML file is malformed');
                    }

                    break;
                default:
                    throw new InvalidArgumentException('Please provide a JSON or YAML file');
            }
        }

        /** @var LoggerService $loggerService */
        $loggerService = $this->getContainer()->offsetGet(LoggerService::SERVICE_ID);
        $loggerService->addLogger(
            new TaoLog(
                [
                    'appenders' => [
                        [
                            'class' => 'SingleFileAppender',
                            'threshold' => common_Logger::TRACE_LEVEL,
                            'file' => TAO_INSTALL_PATH . 'tao/install/log/install.log',
                        ],
                    ],
                ]
            )
        );

        $options = [
            'install_sent' => '1',
            'module_host' => 'tao.local',
            'module_lang' => 'en-US',
            'module_mode' => 'debug',
            'module_name' => 'mytao',
            'module_namespace' => '',
            'module_url' => '',
            'submit' => 'Install',
            'user_email' => '',
            'user_firstname' => '',
            'user_lastname' => '',
            'user_login' => '',
            'user_pass' => '',
            'instance_name' => null,
            'extensions' => null,
            'timezone' => date_default_timezone_get(),
            'extra_persistences' => [],
        ];

        if (!isset($parameters['configuration'])) {
            throw new InvalidArgumentException('Your config should have a "configuration" key');
        }

        if (!isset($parameters['configuration']['generis'])) {
            throw new InvalidArgumentException('Your config should have a "generis" key under "configuration"');
        }

        if (!isset($parameters['configuration']['global'])) {
            throw new InvalidArgumentException('Your config should have a "global" key under "configuration"');
        }

        $global = $parameters['configuration']['global'];
        $options['module_namespace'] = $global['namespace'];
        $options['instance_name'] = $global['instance_name'];
        $options['module_url'] = $global['url'];
        $options['module_lang'] = $global['lang'];
        $options['module_mode'] = $global['mode'];
        $options['timezone'] = $global['timezone'];
        $options['import_local'] = $global['import_data'] ?? false === true;

        $rootDir = dir(__DIR__ . '/../../');
        $options['root_path'] = $global['root_path'] ?? realpath($rootDir->path) . DIRECTORY_SEPARATOR;

        $options['file_path'] = $global['file_path'] ?? $options['root_path'] . 'data' . DIRECTORY_SEPARATOR;

        if (isset($global['session_name'])) {
            $options['session_name'] = $global['session_name'];
        }

        if (isset($global['anonymous_lang'])) {
            $options['anonymous_lang'] = $global['anonymous_lang'];
        }

        // Get extensions to install
        if (isset($parameters['extensions'])) {
            $options['extensions'] = $parameters['extensions'];
        }

        if (!isset($parameters['super-user'])) {
            throw new InvalidArgumentException('Your config should have a "global" key under "generis"');
        }

        $superUser = $parameters['super-user'];
        $options['user_login'] = $superUser['login'];
        $options['user_pass1'] = $superUser['password'];

        if (isset($parameters['lastname'])) {
            $options['user_lastname'] = $parameters['lastname'];
        }

        if (isset($parameters['firstname'])) {
            $options['user_firstname'] = $parameters['firstname'];
        }

        if (isset($parameters['email'])) {
            $options['user_email'] = $parameters['email'];
        }

        $installOptions = [
            'root_path' => $options['root_path'],
            'install_path' => $options['root_path'] . 'tao/install/',
        ];

        if (isset($global['installation_config_path'])) {
            $installOptions['installation_config_path'] = $global['installation_config_path'];
        }

        // Run the actual install
        if ($this->getContainer() instanceof Container) {
            $this->getContainer()->offsetSet(tao_install_Installator::CONTAINER_INDEX, $installOptions);
            $installer = new tao_install_Installator($this->getContainer());
        } else {
            $installer = new tao_install_Installator($installOptions);
        }

        $serviceManager = $installer->getServiceManager();

        if (!isset($parameters['configuration']['generis']['persistences'])) {
            throw new InvalidArgumentException('Your config should have a "persistence" key under "generis"');
        }

        $persistences = $parameters['configuration']['generis']['persistences'];

        if (isset($persistences['default'])) {
            $parameters['configuration']['generis']['persistences'] = $this->wrapPersistenceConfig($persistences);
        } elseif (!isset($persistences['type'])) {
            throw new InvalidArgumentException('Your config should have a "type" key under "persistences"');
        }

        foreach ($parameters['configuration'] as $extension => $configs) {
            foreach ($configs as $key => $config) {
                if (!isset($config['type']) || $config['type'] !== 'configurableService') {
                    continue;
                }

                $className = $config['class'];
                $params = $config['options'];

                if (!is_a($className, ConfigurableService::class, true)) {
                    $this->logWarning(
                        sprintf(
                            'The class "%s" can not be set as a Configurable Service',
                            $className
                        )
                    );
                    $this->logWarning(
                        'Make sure your configuration is correct and all required libraries are installed'
                    );

                    continue;
                }

                $service = is_a($className, InjectionAwareService::class, true)
                    ? new $className(...$this->prepareParameters($className, $params, $serviceManager))
                    : new $className($params);

                $serviceManager->register(
                    sprintf('%s/%s', $extension, $key),
                    $service
                );
            }
        }

        // Mod rewrite cannot be detected in CLI Mode
        $installer->escapeCheck('custom_tao_ModRewrite');
        $logger = $this->getLogger();

        $installer->install(
            $options,
            function () use ($serviceManager, $parameters, $logger): void {
                /** @var common_ext_ExtensionsManager $extensionManager */
                $extensionManager = $serviceManager->get(common_ext_ExtensionsManager::SERVICE_ID);

                foreach ($parameters['configuration'] as $ext => $configs) {
                    foreach ($configs as $key => $config) {
                        if (
                            (isset($config['type']) && $config['type'] === 'configurableService')
                            || $extensionManager->getInstalledVersion($ext) === null
                        ) {
                            continue;
                        }

                        $extension = $extensionManager->getExtensionById($ext);

                        if (
                            $extension->hasConfig($key)
                            || $extension->getConfig($key) instanceof ConfigurableService
                            || $extension->setConfig($key, $config)
                        ) {
                            continue;
                        }

                        throw new ErrorException(sprintf('Your config %s/%s cannot be set', $ext, $key));
                    }
                }

                // Execute post install scripts
                foreach ($parameters['postInstall'] ?? [] as $script) {
                    if (!isset($script['class']) || !is_a($script['class'], Action::class, true)) {
                        continue;
                    }

                    $object = new $script['class']();

                    if (is_a($object, ServiceLocatorAwareInterface::class)) {
                        $object->setServiceLocator($serviceManager);
                    }

                    $params = (isset($script['params']) && is_array($script['params'])) ? $script['params'] : [];
                    $report = call_user_func($object, $params);

                    if ($report instanceof ReportInterface) {
                        $logger->info(helpers_Report::renderToCommandline($report));
                    }
                }

                $logger->notice('Installation completed!');
            }
        );
    }

    /**
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    private function prepareParameters(string $class, array $parametersToSort, ServiceManager $serviceManager): array
    {
        $reflectionClass = new ReflectionClass($class);
        $constructParameters = $reflectionClass->getMethod('__construct')->getParameters();
        $sortedParameters = [];

        while($constructParameters && $parametersToSort) {
            $parameter = array_shift($constructParameters);
            $parameterName = $parameter->getName();

            try {
                $paramValue = $parametersToSort[$parameterName] ?? $parameter->getDefaultValue();
                $sortedParameters[] = $this->resolveParameter($parameter, $paramValue, $serviceManager);

                unset($parametersToSort[$parameterName]);
            } catch (ReflectionException $exception) {
                throw new RuntimeException(
                    sprintf('No default value for `$%s` argument in %s::__construct', $parameterName, $class)
                );
            }
        }

        if ($parametersToSort) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid arguments `%s` specified for %s',
                    implode(', ', array_keys($parametersToSort)),
                    $class
                )
            );
        }

        return $sortedParameters;
    }

    private function resolveParameter(ReflectionParameter $parameter, $paramValue, ServiceManager $serviceManager)
    {
        if (
            is_string($paramValue)
            && $parameter->getClass() !== null
            && $serviceManager->has($paramValue)
        ) {
            $paramValue = $serviceManager->get($paramValue);
        }

        return $paramValue;
    }

    /**
     * Transforms the seed persistence configuration into command line parameters
     * and then back into a persistence configuration to ensure backwards compatibility
     * with the previous process
     */
    private function wrapPersistenceConfig(array $persistences): array
    {
        if ($this->isMasterSlaveConnection($persistences['default'])) {
            $defaultPersistence = [
                'driver' => 'dbal',
                'connection' => $persistences['default']['connection'],
            ];
        } else {
            $installParams = $this->getCommandLineParameters($persistences['default']);

            $dbalConfigCreator = new tao_install_utils_DbalConfigCreator();
            $defaultPersistence = $dbalConfigCreator->createDbalConfig($installParams);
        }

        if (isset($persistences['default']['sqlLoggerClass'])) {
            $defaultPersistence['sqlLoggerClass'] = $persistences['default']['sqlLoggerClass'];
        }

        $persistences['default'] = $defaultPersistence;

        return [
            'type' => 'configurableService',
            'class' => PersistenceManager::class,
            'options' => [
                'persistences' => $persistences,
            ],
        ];
    }

    private function getCommandLineParameters(array $defaultPersistenceConfig): array
    {
        if (isset($defaultPersistenceConfig['connection'])) {
            $options['db_driver'] = $defaultPersistenceConfig['connection']['driver'];

            if (isset($defaultPersistenceConfig['connection']['driverClass'])) {
                $options['db_driverClass'] = $defaultPersistenceConfig['connection']['driverClass'];
            }

            if (isset($defaultPersistenceConfig['connection']['driverOptions'])) {
                $options['db_driverOptions'] = $defaultPersistenceConfig['connection']['driverOptions'];
            }

            if (isset($defaultPersistenceConfig['connection']['instance'])) {
                $options['db_instance'] = $defaultPersistenceConfig['connection']['instance'];
            }

            $options['db_host'] = $defaultPersistenceConfig['connection']['host'];
            $options['db_name'] = $defaultPersistenceConfig['connection']['dbname'];

            if (isset($defaultPersistenceConfig['connection']['user'])) {
                $options['db_user'] = $defaultPersistenceConfig['connection']['user'];
            }

            if (isset($defaultPersistenceConfig['connection']['password'])) {
                $options['db_pass'] = $defaultPersistenceConfig['connection']['password'];
            }
        } else {
            $options['db_driver'] = $defaultPersistenceConfig['driver'];
            $options['db_host'] = $defaultPersistenceConfig['host'];
            $options['db_name'] = $defaultPersistenceConfig['dbname'];

            if (isset($defaultPersistenceConfig['user'])) {
                $options['db_user'] = $defaultPersistenceConfig['user'];
            }

            if (isset($defaultPersistenceConfig['password'])) {
                $options['db_pass'] = $defaultPersistenceConfig['password'];
            }
        }

        return $options;
    }

    private function isMasterSlaveConnection(array $defaultPersistenceConfig): bool
    {
        return
            isset($defaultPersistenceConfig['connection']['wrapperClass'])
            && is_a(
                $defaultPersistenceConfig['connection']['wrapperClass'],
                MasterSlaveConnection::class,
                true
            );
    }
}
