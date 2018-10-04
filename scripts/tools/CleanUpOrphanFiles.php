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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\scripts\tools;

use common_report_Report;
use oat\generis\model\fileReference\ResourceFileSerializer;
use oat\generis\model\GenerisRdf;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\extension\script\ScriptAction;
use oat\oatbox\filesystem\Directory;

/**
 * sudo -u www-data php index.php 'oat\tao\scripts\tools\CleanUpOrphanFiles'
 */
class CleanUpOrphanFiles extends ScriptAction
{
    private $wetRun = false;
    private $verbose = false;
    private $removedCount = 0;

    use OntologyAwareTrait;

    protected function provideOptions()
    {
        return [
            'wet-run' => [
                'prefix' => 'w',
                'flag' => true,
                'longPrefix' => 'wet-run',
                'description' => 'Find and remove all orphan triples related to files for removed items.',
            ],
            'verbose' => [
                'prefix' => 'v',
                'flag' => true,
                'longPrefix' => 'Verbose',
                'description' => 'Force script to be more details',
            ],
        ];
    }

    protected function provideDescription()
    {
        return 'Tool to remove orphan files attached to removed items. By default in dry-run';
    }

    protected function provideUsage()
    {
        return [
            'prefix' => 'h',
            'longPrefix' => 'help',
            'description' => 'Prints a help statement'
        ];
    }

    /**
     * @return common_report_Report
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     * @throws \oat\search\base\exception\SearchGateWayExeption
     * @throws \common_exception_Error
     */
    protected function run()
    {
        $this->init();

        $report = common_report_Report::createInfo('Following files');

        /** @var ResourceFileSerializer $serializer */
        $serializer = $this->getServiceManager()->get(ResourceFileSerializer::SERVICE_ID);

        /** @var ComplexSearchService $search */
        $search = $this->getServiceManager()->get(ComplexSearchService::class);
        $builder = $search->getGateway()->query();

        $list = $search->searchType($builder, GenerisRdf::CLASS_GENERIS_FILE, true);

        $builder->setCriteria($list);

        $resultSet = $search->getGateway()->search($builder);

        $report->add(new common_report_Report(common_report_Report::TYPE_SUCCESS, sprintf('%s Total Files Found in RDS, where: ', $resultSet->total())));

        $affectedCount = 0;
        $errorsCount = 0;
        $orphansCount = 0;
        $redundantCount = 0;

        /** @var \core_kernel_classes_Resource $resource */
        foreach ($resultSet as $resource) {

            try {

                $file = $serializer->unserialize($resource);

                $isDirectory = $file instanceof Directory;
                $isRedundant = !$isDirectory && in_array($file->getBasename(), $this->getRedundantFiles());

                if ($isRedundant) {
                    $redundantCount++;
                    if ($this->verbose) {
                        $report->add(new common_report_Report(common_report_Report::TYPE_INFO, sprintf('URI %s : File %s', $resource->getUri(), $file->getPrefix())));
                    }
                    $this->remove($resource);
                    continue;
                }

                $isOrphan = $this->isOrphan($resource);

                if ($isOrphan) {
                    $orphansCount++;

                    if (!$file->exists()) {
                        if ($this->verbose) {
                            $report->add(new common_report_Report(common_report_Report::TYPE_INFO, sprintf('URI %s : File %s', $resource->getUri(), $file->getPrefix())));
                        }
                        $this->remove($resource);
                        $affectedCount++;
                    }
                }
            } catch (\Exception $exception) {
                $errorsCount++;
                $report->add(common_report_Report::createFailure($exception->getMessage()));
            }

        }
        $report->add(new common_report_Report(common_report_Report::TYPE_SUCCESS, sprintf('%s redundant at RDS', $redundantCount)));
        $report->add(new common_report_Report(common_report_Report::TYPE_SUCCESS, sprintf('%s orphans at FS', $orphansCount)));
        $report->add(new common_report_Report(common_report_Report::TYPE_SUCCESS, sprintf('%s missing at FS', $affectedCount)));
        $report->add(new common_report_Report(common_report_Report::TYPE_SUCCESS, sprintf('%s removed at FS', $this->removedCount)));

        if ($errorsCount) {
            $report->add(new common_report_Report(common_report_Report::TYPE_ERROR, sprintf('%s errors happened, check details above', $errorsCount)));
        }
//        $report->add(new common_report_Report(common_report_Report::TYPE_SUCCESS, memory_get_peak_usage()/1024/1024));

        return $report;
    }

    private function init()
    {
        if ($this->getOption('wet-run')) {
            $this->wetRun = true;
        }
        $this->verbose = $this->getOption('verbose');

    }

    protected function showTime()
    {
        return true;
    }

    /**
     * @param \core_kernel_classes_Resource $resource
     * @return bool
     */
    private function isOrphan(\core_kernel_classes_Resource $resource)
    {
        $sql = 'SELECT subject FROM statements s WHERE s.object=?';
        $stmt = $this->getPersistence()->query($sql, [$resource->getUri()]);
        $res = $stmt->fetchAll();

        return 0 === count($res);
    }

    private function getPersistence()
    {
        return $this->getServiceManager()
            ->get(\common_persistence_Manager::SERVICE_ID)
            ->getPersistenceById('default');
    }

    private function getRedundantFiles()
    {
        return [
            'qti.xml' //special case, see linked story ( has been stored at RDS, but never referenced via resource ).
        ];
    }

    /**
     * @param $resource
     * @return void
     */
    protected function remove(\core_kernel_classes_Resource $resource)
    {
        if ($this->wetRun) {
            $resource->delete();
            $this->removedCount++;
            $this->getLogger()->info(sprintf('%s has been removed', $resource->getUri()));
        }
    }

}