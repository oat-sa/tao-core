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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\task\migration\service;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\task\migration\MigrationConfig;

class ResultFilterFactory extends ConfigurableService implements ResultFilterFactoryInterface
{
    /** @var MigrationConfig */
    protected $config;

    public function create(MigrationConfig $config): ResultFilter
    {
        $this->config = $config;

        $max = $this->getMax();
        $end = $this->calculateEndPosition(
            (int)$config->getCustomParameter('start'),
            $config->getChunkSize(),
            $max
        );

        return new ResultFilter(
            array_merge(
                $config->getCustomParameters(),
                [
                    'start' => (int)$config->getCustomParameter('start'),
                    'end' => $end,
                    'max' => $max
                ]
            )
        );
    }

    protected function getMax(): int
    {
        return $this->getStatementLastIdRetriever()->retrieve();
    }

    private function calculateEndPosition(int $start, int $chunkSize, int $max): int
    {
        $end = $start + $chunkSize;

        if ($end >= $max) {
            $end = $max;
        }

        return $end;
    }

    private function getStatementLastIdRetriever(): StatementLastIdRetriever
    {
        return $this->getServiceLocator()->get(StatementLastIdRetriever::class);
    }
}
