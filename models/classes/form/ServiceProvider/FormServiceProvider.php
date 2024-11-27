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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\form\ServiceProvider;

use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\featureFlag\Service\FeatureFlagPropertiesMapping;
use oat\tao\model\form\Modifier\UniqueIdFormModifier;
use oat\tao\model\TaoOntology;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class FormServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $services
            ->get(FeatureFlagPropertiesMapping::class)
            ->call(
                'addFeatureProperty',
                [
                    'FEATURE_FLAG_UNIQUE_NUMERIC_QTI_IDENTIFIER',
                    TaoOntology::PROPERTY_UNIQUE_IDENTIFIER,
                ]
            );

        $services
            ->set(UniqueIdFormModifier::class, UniqueIdFormModifier::class)
            ->args([
                service(FeatureFlagChecker::class),
                service(FeatureFlagPropertiesMapping::class),
            ]);
    }
}
