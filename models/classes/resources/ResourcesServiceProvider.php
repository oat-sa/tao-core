<?php

declare(strict_types=1);

namespace oat\tao\model\resources;

use oat\generis\model\data\Ontology;
use oat\tao\model\resources\Service\ClassDeleter;
use oat\tao\model\accessControl\PermissionChecker;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class ResourcesServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $services
            ->set(ClassDeleter::class, ClassDeleter::class)
            ->public()
            ->args(
                [
                    service(PermissionChecker::class),
                    service(Ontology::SERVICE_ID),
                ]
            );
    }
}
