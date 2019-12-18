<?php


namespace oat\tao\scripts\tools;

use common_report_Report as Report;
use oat\oatbox\action\Action;
use oat\oatbox\service\ServiceConfigDriver;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\di\Container;
use oat\tao\model\di\ContainerBuilder as diContainerBuilder;
use oat\tao\model\di\LegacyServiceLoader;
use oat\tao\model\LegacySMStorage;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Dotenv\Dotenv;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;


class ContainerBuilder implements Action, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function __invoke($params)
    {
        $configuration = $params['configuration'] ?? 'config/generis.conf.php';
        $forceRebuild = $params['force'] ?? true;
        $start = time();
        $this->buildConfiguration($configuration, $forceRebuild);

        $report = new Report(Report::TYPE_SUCCESS, 'Container rebuild');
        if ($forceRebuild) {
            $report->add(
                new Report(
                    Report::TYPE_INFO,
                    sprintf(
                        'Time - %s seconds, Peak Memory - %s Mb ',
                        time() - $start,
                        round(memory_get_peak_usage() / 1024 / 1024, 2)
                    )
                )
            );
        }
        return $report;
    }

    private function buildConfiguration($configuration, $forceRebuild = false)
    {
        $envFile = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
            . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '.env';
        if (file_exists($envFile)) {
            $dotenv = new Dotenv();
            $dotenv->loadEnv($envFile);
        }

        if (!is_string($configuration) || !is_readable($configuration)) {
            throw new \common_exception_PreConditionFailure('TAO platform seems to be not installed.');
        }

        require_once $configuration;
        $serviceManager = new ServiceManager(
            (new ServiceConfigDriver())->connect(
                'config',
                array(
                    'dir' => dirname($configuration),
                    'humanReadable' => true
                )
            )
        );

        $diContainer = $this->getDiContainer($serviceManager, $forceRebuild);
        $this->setServiceLocator($diContainer);
        // To be removed when getServiceManager will disappear
        ServiceManager::setServiceManager($diContainer);
    }

    private function getDiContainer($legacyContainer, $forceDiRebuild = false)
    {
        $file = GENERIS_CACHE_PATH . '/_di/container.php';
        $containerConfigCache = new ConfigCache($file, DEBUG_MODE);

        LegacySMStorage::setServiceManager($legacyContainer);

        if ($forceDiRebuild || !$containerConfigCache->isFresh()) {
            $containerBuilder = new diContainerBuilder();
            $loaderResolver = new LoaderResolver(
                [
                    new YamlFileLoader($containerBuilder, new FileLocator(CONFIG_PATH . 'tao')),
                    new LegacyServiceLoader($containerBuilder, new FileLocator(CONFIG_PATH)),
                ]
            );
            $delegatingLoader = new DelegatingLoader($loaderResolver);
            $delegatingLoader->load('yml/services.yaml');
            $delegatingLoader->load(CONFIG_PATH . '*/*.conf.php');

            $containerBuilder->compile();

            $dumper = new PhpDumper($containerBuilder);
            $containerConfigCache->write(
                $dumper->dump(['class' => 'MyCachedContainer', 'base_class' => Container::class]),
                $containerBuilder->getResources()
            );
        }

        require_once $file;
        $container = new \MyCachedContainer();

        return $container;
    }

}
