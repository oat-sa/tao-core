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

namespace oat\tao\model\search;

use oat\generis\model\data\permission\PermissionHelper;
use oat\generis\model\data\permission\PermissionInterface;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;

class ResultSetResponseNormalizer extends ConfigurableService
{
    use OntologyAwareTrait;

    public function normalize(SearchQuery $searchQuery, ResultSet $resultSet, string $structure): array
    {
        $totalPages = is_null($searchQuery->getRows()) || $searchQuery->getRows() === 0
            ? 1
            : ceil($resultSet->getTotalCount() / $searchQuery->getRows());

        $resultsRaw = $resultSet->getArrayCopy();

        $accessibleResultsMap = [];

        $resultAmount = count($resultsRaw);

        $response = [];
        if ($resultAmount > 0) {
            $accessibleResultsMap = array_flip(
                $this->getPermissionHelper()->filterByPermission(array_column($resultsRaw, 'id'), PermissionInterface::RIGHT_READ)
            );

            foreach ($resultsRaw as $content) {
                $isAccessible = isset($accessibleResultsMap[$content['id']]);

                if (!$isAccessible) {
                    $content['label'][] = __('Access Denied');
                    continue;
                }

                $instanceProperties[] = $this->getResultSetMapper()->getResultSetModel($content, $structure);

                $response['data'] = $instanceProperties;
            }
        }
        $response['readonly'] = array_fill_keys(
            array_keys(
                array_diff_key(
                    $resultsRaw,
                    $accessibleResultsMap
                )
            ),
            true
        );

        $response['success'] = true;
        $response['page'] = empty($response['data']) ? 0 : $searchQuery->getPage();
        $response['total'] = $totalPages;

        $response['totalCount'] = $resultSet->getTotalCount();

        $response['records'] = $resultAmount;

        return $response;
    }

    private function getPermissionHelper(): PermissionHelper
    {
        return $this->getServiceLocator()->get(PermissionHelper::class);
    }

    private function getResultSetMapper(): ResultSetMapper
    {
        return $this->getServiceLocator()->get(ResultSetMapper::SERVICE_ID);
    }
}
