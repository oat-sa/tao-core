<?php

declare(strict_types=1);

namespace oat\tao\model\ParamConverter\ServiceProvider;

use oat\tao\model\ParamConverter\Request\QueryParamConverter;
use oat\tao\model\ParamConverter\Request\ParamConverterManager;
use oat\tao\model\ParamConverter\EventListener\ParamConverterListener;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

class ParamConverterServiceProvider implements ContainerServiceProviderInterface
{
    private const PARAM_CONVERTERS = [
        QueryParamConverter::class,
    ];

    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();
        $parameters = $configurator->parameters();

        $paramConvertersReferences = [];

        foreach (self::PARAM_CONVERTERS as $paramConverter) {
            $services
                ->set($paramConverter, $paramConverter)
                ->public();

            $paramConvertersReferences[] = service($paramConverter);
        }

        $services
            ->set(ParamConverterManager::class, ParamConverterManager::class)
            ->public()
            ->args(
                [
                    $paramConvertersReferences,
                ]
            );

        $parameters->set('autoConvert', true);
        $services
            ->set(ParamConverterListener::class, ParamConverterListener::class)
            ->public()
            ->args(
                [
                    service(ParamConverterManager::class),
                    param('autoConvert')
                ]
            );
    }
}
