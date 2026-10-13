<?php

declare(strict_types=1);

namespace oat\tao\model\lockDownBrowser\ServiceProvider;

use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use oat\tao\model\lockDownBrowser\Service\HttpRequestLockDownBrowserDetector;
use oat\tao\model\lockDownBrowser\Service\LockDownBrowserDetectionService;
use oat\tao\model\lockDownBrowser\Service\LockDownBrowserDetectorInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/**
 * @codeCoverageIgnore
 */
class LockDownBrowserServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $services
            ->set(HttpRequestLockDownBrowserDetector::class, HttpRequestLockDownBrowserDetector::class)
            ->public();

        $services
            ->set(LockDownBrowserDetectorInterface::class, HttpRequestLockDownBrowserDetector::class)
            ->public();

        $services
            ->set(LockDownBrowserDetectionService::class, LockDownBrowserDetectionService::class)
            ->public()
            ->args([service(LockDownBrowserDetectorInterface::class)]);

        $services
            ->alias(LockDownBrowserDetectionService::SERVICE_ID, LockDownBrowserDetectionService::class)
            ->public();
    }
}
