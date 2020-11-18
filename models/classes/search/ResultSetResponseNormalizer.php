<?php


namespace oat\tao\model\search;


use oat\generis\model\data\permission\PermissionHelper;
use oat\generis\model\data\permission\PermissionInterface;
use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\OntologyRdfs;
use oat\oatbox\service\ConfigurableService;

class ResultSetResponseNormalizer extends ConfigurableService
{
    use OntologyAwareTrait;

    public function normalize(SearchQuery $searchQuery, ResultSet $resultSet): array
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
                $this->getPermissionHelper()->filterByPermission($resultsRaw, PermissionInterface::RIGHT_READ)
            );

            foreach ($resultsRaw as $uri) {
                $instance = $this->getResource($uri);
                $isAccessible = isset($accessibleResultsMap[$uri]);

                if (!$isAccessible) {
                    $instance->label = __('Access Denied');
                }

                $instanceProperties = [
                    'id' => $instance->getUri(),
                    OntologyRdfs::RDFS_LABEL => $instance->getLabel(),
                ];

                $response['data'][] = $instanceProperties;
            }
        }
        $response['readonly'] = array_fill_keys(
            array_keys(
                array_diff_key(
                    array_flip($resultsRaw),
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

}