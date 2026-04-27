<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 31 Milk St # 960789 Boston, MA 02196 USA.
 *
 * Copyright (c) 2022-2026 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\accessControl;

use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use oat\tao\model\accessControl\Service\AccessTokenService;
use oat\tao\model\accessControl\Service\ConfigurationService;
use oat\tao\model\accessControl\Service\DeleteRoleService;
use oat\tao\model\accessControl\Service\InternalRoleSpecification;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use tao_models_classes_RoleService;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class AccessControlServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $services->set(InternalRoleSpecification::class, InternalRoleSpecification::class);

        $services->set(tao_models_classes_RoleService::class, tao_models_classes_RoleService::class)
            ->public()
            ->factory(tao_models_classes_RoleService::class . '::singleton');

        $services->set(DeleteRoleService::class, DeleteRoleService::class)
            ->public()
            ->args(
                [
                    service(InternalRoleSpecification::class),
                    service(tao_models_classes_RoleService::class)
                ]
            );

        $services->set(RoleBasedContextRestrictAccess::class, RoleBasedContextRestrictAccess::class)
            ->public()
            ->args([[]]);

        $services->set(AccessTokenService::class, AccessTokenService::class)
            ->public();

        $services->set(ConfigurationService::class, ConfigurationService::class)
            ->public()
            ->args([service(AccessTokenService::class)]);
    }
}
