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

use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use oat\generis\model\data\permission\PermissionHelper;
use oat\generis\model\data\permission\PermissionInterface;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\TaoOntology;

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

        $permissionHelper = $this->getPermissionHelper();

        if ($resultAmount > 0) {
            $accessibleResultsMap = array_flip(
                $permissionHelper
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
                    $content['label'] = __('Access Denied');
                }

                $resource = $this->getResource($content['id']);

                $readonly = false;

                $topLevelClass = $this->getClass(TaoOntology::CLASS_URI_OBJECT);

                foreach ($resource->getTypes() as $type) {
                    $accessibleResources = $permissionHelper
                    ->filterByPermission(
                        array($type->getUri()),
                        PermissionInterface::RIGHT_READ
                    );

                    if (empty($accessibleResources) || !$isAccessible) {
                        $readonly = true;
                        break;
                    }

                    $class = $this->getClass($type->getUri());
                    $readonly = $this->checkParentClassPermission($class, $permissionHelper, $topLevelClass);

                    if ($readonly === true) {
                        break;
                    }
                }

                $response['data'][] = $this->getResultSetFilter()->filter($content, $structure);

                $readonlyArray[$content['id']] = $readonly;
            }
        }

        $response['readonly'] = $readonlyArray;

        $response['success'] = true;
        $response['page'] = empty($response['data']) ? 0 : $searchQuery->getPage();
        $response['total'] = $totalPages;

        $response['totalCount'] = $resultSet->getTotalCount();

        $response['records'] = $resultAmount;

        return $response;
    }

    private function checkParentClassPermission(core_kernel_classes_Class $class, PermissionHelper $permissionHelper, core_kernel_classes_Class $topLevelClass): bool
    {
        $parentClasses = $class->getParentClasses(true);

        foreach ($parentClasses as $parentClass) {
            $accessibleResource = $permissionHelper
            ->filterByPermission(
                array($parentClass->getUri()),
                PermissionInterface::RIGHT_READ
            );

            if (empty($accessibleResource)) {
                return true;
            }

            if ($parentClass->getUri() == $topLevelClass->getUri()) {
                return false;
            }
        }
        return false;
    }

    private function getPermissionHelper(): PermissionHelper
    {
        return $this->getServiceLocator()->get(PermissionHelper::class);
    }

    private function getResultSetFilter(): ResultSetFilter
    {
        return $this->getServiceLocator()->get(ResultSetFilter::class);
    }
}
