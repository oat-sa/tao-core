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
    /**
     * Default values as container parameters. Symfony's env()->default() requires a parameter name,
     * not a literal (see EnvVarProcessor "default" processor); these are the fallbacks when the
     * corresponding env vars are not set.
     */
    private const PARAM_MAX_RETRIES = 'TAO_ID_GENERATOR_MAX_RETRIES_DEFAULT';
    private const PARAM_SHOULD_CHECK_STATEMENTS = 'TAO_ID_GENERATOR_SHOULD_CHECK_STATEMENTS_DEFAULT';
    private const PARAM_ID_START = 'TAO_ID_GENERATOR_ID_START_DEFAULT';

    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();
        $parameters = $configurator->parameters();

        $parameters->set(self::PARAM_MAX_RETRIES, 100);
        $parameters->set(self::PARAM_SHOULD_CHECK_STATEMENTS, true);
        $parameters->set(self::PARAM_ID_START, 1);

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
                env('TAO_ID_GENERATOR_MAX_RETRIES')->default(self::PARAM_MAX_RETRIES)->int(),
                env('TAO_ID_GENERATOR_SHOULD_CHECK_STATEMENTS')->default(self::PARAM_SHOULD_CHECK_STATEMENTS)->bool(),
                env('TAO_ID_GENERATOR_ID_START')->default(self::PARAM_ID_START)->int(),
            ]);

        $services
            ->set(IdentifierGeneratorProxy::class, IdentifierGeneratorProxy::class)
            ->public()
            ->args([
                service(Ontology::SERVICE_ID),
            ]);
    }
}
