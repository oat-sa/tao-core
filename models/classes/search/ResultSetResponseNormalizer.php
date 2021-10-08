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
 * Copyright (c) 2020-2021 (original work) Open Assessment Technologies SA;
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

        $resourcePermissions = [];

        $resultAccessChecker = $this->getResultAccessChecker();

        if ($resultAmount > 0) {
            $accessibleResultsMap = array_flip(
                $this->getPermissionHelper()
                    ->filterByPermission(
                        array_column($resultsRaw, 'id'),
                        PermissionInterface::RIGHT_READ
                    )
            );

            foreach ($resultsRaw as $content) {
                if (!is_array($content)) {
                    $this->logError(
                        sprintf(
                            'Search content issue detected: expected array, but %s given',
                            json_encode($content)
                        )
                    );
                    continue;
                }

                $isAccessible = isset($accessibleResultsMap[$content['id']]);

                if (!$isAccessible) {
                    $hasReadAccess = false;
                }

                if ($isAccessible) {
                    $hasReadAccess = $resultAccessChecker->hasReadAccess($content);
                }

                if ($hasReadAccess === false) {
                    $content['label'] = __('Access Denied');
                    $content['id'] = '';
                }

                $resourcePermissions[$content['id']] = !$hasReadAccess;

                $response['data'][] = $this->getResultSetFilter()->filter($content, $structure);
            }
        }

        $response['readonly'] = $resourcePermissions;
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

    private function getResultSetFilter(): ResultSetFilter
    {
        return $this->getServiceLocator()->get(ResultSetFilter::class);
    }

    private function getResultAccessChecker(): ResultAccessChecker
    {
        return $this->getServiceLocator()->get(ResultAccessChecker::class);
    }
}
