<?php

namespace oat\tao\model\IdentifierGenerator\ServiceProvider;

use oat\generis\model\data\Ontology;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\generis\persistence\PersistenceManager;
use oat\oatbox\log\LoggerService;
use oat\tao\model\IdentifierGenerator\Generator\IdentifierGeneratorProxy;
use oat\tao\model\IdentifierGenerator\Generator\NumericIdentifierGenerator;
use oat\tao\model\IdentifierGenerator\Repository\UniqueIdRepository;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

class IdentifierGeneratorServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $services->set(UniqueIdRepository::class, UniqueIdRepository::class)
            ->public()
            ->args([
                service(PersistenceManager::class),
                service(LoggerService::SERVICE_ID),
                'default'
            ]);

        $services->set(NumericIdentifierGenerator::class, NumericIdentifierGenerator::class)
            ->args([
                service(UniqueIdRepository::class),
                service(ComplexSearchService::class),
                env('TAO_ID_GENERATOR_MAX_RETRIES')->default('')->int(),
                env('TAO_ID_GENERATOR_SHOULD_CHECK_STATEMENTS')->default('')->bool(),
                env('TAO_ID_GENERATOR_ID_START')->default('')->int(),
            ]);

        $services
            ->set(IdentifierGeneratorProxy::class, IdentifierGeneratorProxy::class)
            ->public()
            ->args([
                service(Ontology::SERVICE_ID),
            ]);
    }
}
