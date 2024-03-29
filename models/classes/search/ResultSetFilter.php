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
 * Copyright (c) 2020-2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\search;

use oat\oatbox\service\ConfigurableService;

/**
 * @deprecated Dynamic columns is managed as a feature for advanced search now.
 *             This class should not be used anymore
 */
class ResultSetFilter extends ConfigurableService
{
    /**
     * @deprecated Dynamic columns is managed as a feature for advanced search now.
     *             This class should not be used anymore
     */
    public function filter(array $content, string $structure): array
    {
        $allowedKeys = $this->getResultSetMapper()->map($structure);

        return array_filter($content, function ($key) use ($allowedKeys) {
            return $key === 'id' || in_array($key, array_keys($allowedKeys), true);
        }, ARRAY_FILTER_USE_KEY);
    }

    private function getResultSetMapper(): ResultSetMapper
    {
        return $this->getServiceLocator()->get(ResultSetMapper::SERVICE_ID);
    }
}
