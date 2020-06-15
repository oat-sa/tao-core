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

use oat\generis\persistence\PersistenceManager;
use oat\oatbox\action\Action;
use oat\oatbox\log\logger\TaoLog;
use oat\oatbox\log\LoggerService;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\service\ServiceManager;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use oat\tao\install\utils\seed\SeedParser;
use oat\tao\install\SeedInstaller;
use Pimple\Psr11\Container as PsrContainer;

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

        $parser = new SeedParser();
        if ($this->getContainer() !== null && $this->getContainer()->offsetExists(static::SETUP_JSON_CONTENT_OFFSET)) {
            $parameters = json_decode($this->getContainer()->offsetGet(static::SETUP_JSON_CONTENT_OFFSET), true);
            if (is_null($parameters)) {
                throw new InvalidArgumentException('Your Setup JSON seed is malformed');
            }
            $seed = $parser->fromArray($parameters);
        } else {
            if (!isset($params[0])) {
                throw new InvalidArgumentException('You should provide a file path');
            }
            $filePath = $params[0];
            if (!file_exists($filePath)) {
                throw new \ErrorException('Unable to find ' . $filePath);
            }
            $parser = new SeedParser();
            $this->logNotice('Loading seed from '.$filePath);
            $seed = $parser->fromFile($filePath);
        }
        $installer = new SeedInstaller();
        $installer->install($seed, new PsrContainer($this->getContainer()));

        $this->logNotice('Installation completed!');
    }

    /**
     * @param string         $class
     * @param array          $parametersToSort
     * @param ServiceManager $serviceManager
     *
     * @return array
     * @throws ReflectionException
     */
    private function prepareParameters(string $class, array $parametersToSort, ServiceManager $serviceManager): array
    {
        $reflectionClass = new ReflectionClass($class);

        $constructParameters = $reflectionClass->getMethod('__construct')->getParameters();

        $sortedParameters = [];

        while($constructParameters && $parametersToSort) {
            $parameter     = array_shift($constructParameters);
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
                sprintf('Invalid arguments `%s` specified for %s', implode(', ', array_keys($parametersToSort)), $class)
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
    private function wrapPersistenceConfig(array $persistences): PersistenceManager
    {
        $installParams = $this->getCommandLineParameters($persistences['default']);
        $dbalConfigCreator = new tao_install_utils_DbalConfigCreator();
        $persistenceConfig = $dbalConfigCreator->createDbalConfig($installParams);
        $persistenceManager = new PersistenceManager();
        $persistenceManager->registerPersistence('default', $persistenceConfig);
        return $persistenceManager;
    }

    private function getCommandLineParameters(array $defaultPersistenceConfig): array
    {
        if (isset($defaultPersistenceConfig['connection'])) {
            if ($this->isMasterSlaveConnection($defaultPersistenceConfig)) {
                $options['db_driver'] = $defaultPersistenceConfig['connection']['driver'];
                $options['db_host'] = $defaultPersistenceConfig['connection']['master']['host'];
                $options['db_name'] = $defaultPersistenceConfig['connection']['master']['dbname'];

                if (isset($defaultPersistenceConfig['connection']['master']['user'])) {
                    $options['db_user'] = $defaultPersistenceConfig['connection']['master']['user'];
                }

                if (isset($defaultPersistenceConfig['connection']['master']['password'])) {
                    $options['db_pass'] = $defaultPersistenceConfig['connection']['master']['password'];
                }
            } else {
                $options['db_driver'] = $defaultPersistenceConfig['connection']['driver'];

                if (isset($defaultPersistenceConfig['connection']['driverClass'])) {
                    $options['db_driverClass'] = $defaultPersistenceConfig['connection']['driverClass'];
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
        return isset($defaultPersistenceConfig['connection']['wrapperClass'])
            && $defaultPersistenceConfig['connection']['wrapperClass'] === '\\Doctrine\\DBAL\\Connections\\MasterSlaveConnection';
    }
}
