<?php

declare(strict_types=1);

namespace oat\tao\model\Serializer;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer as SymfonySerializerAlias;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use Symfony\Component\Serializer\SerializerInterface as SymfonySerializerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class SerializerServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $services->set(ObjectNormalizer::class, ObjectNormalizer::class);
        $services->set(JsonEncoder::class, JsonEncoder::class);

        $services
            ->set(SymfonySerializerInterface::class, SymfonySerializerAlias::class)
            ->args(
                [
                    [
                        service(ObjectNormalizer::class),
                    ],
                    [
                        service(JsonEncoder::class),
                    ],
                ]
            );

        $services
            ->set(Serializer::class, Serializer::class)
            ->args(
                [
                    service(SymfonySerializerInterface::class),
                ]
            );
    }
}
