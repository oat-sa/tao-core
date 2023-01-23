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

use oat\generis\model\data\permission\PermissionHelper;
use oat\generis\model\data\permission\PermissionInterface;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;

class ResultSetResponseNormalizer extends ConfigurableService
{
    use OntologyAwareTrait;

    /**
     * @inheritDoc
     */
    public function normalize(SearchQuery $searchQuery, ResultSet $resultSet, string $structure): array
    {
        $resultsRaw = $resultSet->getArrayCopy();
        $resultAmount = count($resultsRaw);
        $resourcePermissions = [];
        $responseData = [];
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
                $resourceId = $content['id'];

                if (!is_array($content)) {
                    $this->logError(
                        sprintf(
                            'Search content issue detected: expected array, but %s given',
                            json_encode($content)
                        )
                    );
                    continue;
                }

                $isAccessible = isset($accessibleResultsMap[$resourceId]);

                if (!$isAccessible) {
                    $hasReadAccess = false;
                }

                if ($isAccessible) {
                    $hasReadAccess = $resultAccessChecker->hasReadAccess($content);
                }

                if ($hasReadAccess === false) {
                    $content = [
                        'label' => __('Access Denied'),
                        'id' => $resourceId,
                    ];
                }

                $resourcePermissions[$resourceId] = !$hasReadAccess;

                $responseData[] = $content;
            }
        }

        return $this->createResponse(
            $responseData,
            $resourcePermissions,
            $searchQuery,
            $resultSet,
            $resultAmount
        );
    }

    /**
     * @inheritDoc
     */
    public function normalizeSafeClass(SearchQuery $searchQuery, ResultSet $resultSet, string $structure): array
    {
        $resultsRaw = $resultSet->getArrayCopy();
        $resultAmount = count($resultsRaw);
        $resourcePermissions = [];
        $responseData = [];

        if ($resultAmount > 0) {
            $accessibleResultsMap = array_flip(
                $this->getPermissionHelper()->filterByPermission(
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

                $resourcePermissions[$content['id']] = !isset($accessibleResultsMap[$content['id']]);
                $responseData[] = $content;
            }
        }

        return $this->createResponse(
            $responseData,
            $resourcePermissions,
            $searchQuery,
            $resultSet,
            $resultAmount
        );
    }

    private function createResponse(
        array $responseData,
        array $resourcePermissions,
        SearchQuery $searchQuery,
        ResultSet $resultSet,
        int $resultAmount
    ): array {
        return [
            'data' => $responseData,
            'readonly' => $resourcePermissions,
            'success' => true,
            'page' => empty($response['data']) ? 0 : $searchQuery->getPage(),
            'total' => empty($searchQuery->getRows())
                ? 1
                : ceil($resultSet->getTotalCount() / $searchQuery->getRows()),
            'totalCount'=> $resultSet->getTotalCount(),
            'records' => $resultAmount,
        ];
    }

    private function getPermissionHelper(): PermissionHelper
    {
        return $this->getServiceLocator()->get(PermissionHelper::class);
    }

    private function getResultAccessChecker(): ResultAccessChecker
    {
        return $this->getServiceLocator()->get(ResultAccessChecker::class);
    }
}
