<?php

declare(strict_types=1);

namespace oat\tao\model\Lists\ServiceProvider;

use oat\generis\persistence\PersistenceManager;
use oat\tao\model\Lists\DataAccess\Repository\DependencyRepository;
use oat\tao\model\Lists\Business\Validation\DependsOnPropertyValidator;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

class ListsServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $services
            ->set(DependencyRepository::class, DependencyRepository::class)
            ->public()
            ->args(
                [
                    service(PersistenceManager::SERVICE_ID),
                ]
            );

        $services
            ->set(DependsOnPropertyValidator::class, DependsOnPropertyValidator::class)
            ->public()
            ->args(
                [
                    service(DependencyRepository::class),
                ]
            );
    }
}
