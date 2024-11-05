<?php

namespace oat\tao\model\IdentifierGenerator\ServiceProvider;

use oat\generis\model\data\Ontology;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\IdentifierGenerator\Generator\IdentifierGeneratorProxy;
use oat\tao\model\IdentifierGenerator\Generator\NumericIdentifierGenerator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class IdentifierGeneratorServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $services->set(NumericIdentifierGenerator::class, NumericIdentifierGenerator::class);

        $services
            ->set(IdentifierGeneratorProxy::class, IdentifierGeneratorProxy::class)
            ->public()
            ->args([
                service(Ontology::SERVICE_ID),
            ]);
    }
}
