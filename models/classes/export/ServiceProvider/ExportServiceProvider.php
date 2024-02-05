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
 * Copyright (c) 2023 (update and modification) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\export\ServiceProvider;

use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use oat\tao\model\export\CustomizedGenerisAdapterRdf;
use oat\tao\model\taskQueue\TaskLog\Decorator\TaskLogEntityDecorateProcessor;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

class ExportServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $services
            ->set(CustomizedGenerisAdapterRdf::class, CustomizedGenerisAdapterRdf::class)
            ->tag('export.entity_decorator');

        $services
            ->set(TaskLogEntityDecorateProcessor::class, TaskLogEntityDecorateProcessor::class)
            ->public();
    }
}
