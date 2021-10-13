<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

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
