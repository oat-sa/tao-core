<?php

declare(strict_types=1);

namespace oat\tao\model\ParamConverter\ServiceProvider;

use oat\tao\model\ParamConverter\Request\QueryParamConverter;
use oat\tao\model\ParamConverter\Manager\ParamConverterManager;
use oat\tao\model\ParamConverter\Configuration\AutoConfigurator;
use oat\tao\model\ParamConverter\EventListener\ParamConverterListener;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ParametersConfigurator;

class ParamConverterServiceProvider implements ContainerServiceProviderInterface
{
    private const PARAM_CONVERTERS = [
        QueryParamConverter::class,
    ];

    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();
        $parameters = $configurator->parameters();

        $this->provideConverters($services);
        $this->provideParamConverterManager($services);
        $this->provideParamConverterListener($services, $parameters);
    }

    private function provideConverters(ServicesConfigurator $services): void
    {
        foreach (self::PARAM_CONVERTERS as $paramConverter) {
            $services
                ->set($paramConverter, $paramConverter)
                ->public();
        }
    }

    private function provideParamConverterManager(ServicesConfigurator $services): void
    {
        $services
            ->set(ParamConverterManager::class, ParamConverterManager::class)
            ->public()
            ->args(
                [
                    array_map(
                        static function (string $paramConverter) {
                            return service($paramConverter);
                        },
                        self::PARAM_CONVERTERS
                    ),
                ]
            );
    }

    private function provideParamConverterListener(
        ServicesConfigurator $services,
        ParametersConfigurator $parameters
    ): void {
        $services
            ->set(AutoConfigurator::class, AutoConfigurator::class)
            ->public();

        $parameters->set('autoConvert', true);
        $services
            ->set(ParamConverterListener::class, ParamConverterListener::class)
            ->public()
            ->args(
                [
                    service(AutoConfigurator::class),
                    service(ParamConverterManager::class),
                    param('autoConvert'),
                ]
            );
    }
}
