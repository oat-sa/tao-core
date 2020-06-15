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
 * Copyright (c) 2019  (original work) Open Assessment Technologies SA;
 *
 * @author Oleksandr Zagovorychev <zagovorichev@gmail.com>
 */

namespace oat\tao\install;

use oat\oatbox\log\LoggerAwareTrait;
use oat\tao\install\utils\seed\Seed;
use Psr\Container\ContainerInterface;
use oat\oatbox\log\LoggerService;
use oat\oatbox\log\logger\TaoLog;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use common_report_Report;
use helpers_Report;
use oat\generis\model\GenerisRdf;
use oat\oatbox\action\Action;
use tao_install_Installator;
use Pimple\Container;

class SeedInstaller
{
    use LoggerAwareTrait;

    public function install(Seed $seed, ContainerInterface $container): void
    {
        $this->configureLogger($container);
        $options = $this->generateParameters($seed);
        $weirdAssContainer = new Container();
        $weirdAssContainer->offsetSet(tao_install_Installator::CONTAINER_INDEX, $options);
        $weirdAssContainer->offsetSet(LoggerService::SERVICE_ID, $container->get(LoggerService::SERVICE_ID));
        $installator = new tao_install_Installator($weirdAssContainer);

        $serviceManager = $installator->getServiceManager();
        foreach($seed->getServices() as $serviceId => $service) {
            $serviceManager->register($serviceId, $service);
        }

        // mod rewrite cannot be detected in CLI Mode.
        $installator->escapeCheck('custom_tao_ModRewrite');
        $options[tao_install_Installator::CONTAINER_INDEX] = $container;
        $installator->install($options);

        $this->runPostInstallScripts($seed->getPostInstallScripts(), $serviceManager);
    }

    private function generateParameters(Seed $seed): array
    {
        $options =  [
            "install_sent"    =>  "1"
            , "module_host" =>      "tao.local"
            , "module_lang" =>      "en-US"
            , "module_mode" =>      "debug"
            , "module_name" =>      "mytao"
            , "module_namespace" => ""
            , "module_url"  =>      ""
            , "submit"  =>          "Install"
            , "user_email"  =>      ""
            , "user_firstname"  =>  ""
            , "user_lastname"   =>  ""
            , "user_login"  =>      ""
            , "user_pass"   =>      ""
            , "instance_name" =>    null
            , "extensions" =>       null
            , 'timezone'   =>      date_default_timezone_get()
            , 'extra_persistences' => []
        ];

        $options['module_namespace'] = $seed->getLocalNamespace();
        $options['instance_name'] = $seed->getInstanceName();
        $options['module_url'] = $seed->getRootUrl();
        $options['module_lang'] = $seed->getDefaultLanguage();
        $options['module_mode'] = $seed->useDebugMode() ? 'debug' : 'production';
        $options['timezone'] = $seed->getDefaultTimezone();
        $options['import_local'] = $seed->installSamples();

        $options['file_path'] = $seed->getLocalFilePath();
        $options['root_path'] = realpath(__DIR__ . '/../..') . DIRECTORY_SEPARATOR;
        $options['install_path'] = __DIR__;

        if (!is_null($seed->getSessionName())) {
            $options['session_name'] = $seed->getSessionName();
        }
        $options['anonymous_lang'] = $seed->getAnonymousLanguage();

        $options['extensions'] = $seed->getExtensionsToInstall();

        $mapping = [
            GenerisRdf::PROPERTY_USER_FIRSTNAME => 'firstname',
            GenerisRdf::PROPERTY_USER_LASTNAME => 'user_lastname',
            GenerisRdf::PROPERTY_USER_LOGIN => 'user_login',
            GenerisRdf::PROPERTY_USER_PASSWORD => 'user_pass1',
            GenerisRdf::PROPERTY_USER_MAIL => 'user_email',
        ];
        foreach ($seed->getUserData() as $key => $value) {
            if (isset($mapping[$key])) {
                $options[$mapping[$key]] = $value;
            }
        }
        return $options;
    }

    private function runPostInstallScripts(array $scripts, ServiceLocatorInterface $serviceLocator): void
    {
        // execute post install scripts
        foreach ($scripts as $script) {
            if (isset($script['class']) && is_a($script['class'], Action::class, true)) {
                $object = new $script['class']();
                if (is_a($object, ServiceLocatorAwareInterface::class)) {
                    $object->setServiceLocator($serviceLocator);
                }
                $params = (isset($script['params']) && is_array($script['params'])) ? $script['params'] : [];
                $report = call_user_func($object, $params);

                if ($report instanceof common_report_Report) {
                    $this->logInfo(helpers_Report::renderToCommandline($report));
                }
            }
        }
    }

    private function configureLogger(ContainerInterface $container): void
    {
        /** @var LoggerService $loggerService */
        $loggerService = $container->get(LoggerService::SERVICE_ID);
        $loggerService->addLogger(
            new TaoLog([
                'appenders' => [
                    [
                        'class' => 'SingleFileAppender',
                        'threshold' => \common_Logger::TRACE_LEVEL,
                        'file' => TAO_INSTALL_PATH . 'tao/install/log/install.log'
                    ]
                ]
            ])
        );
        $this->setLogger($loggerService);
    }
}
