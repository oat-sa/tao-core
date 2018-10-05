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

use common_report_Report as Report;
use core_kernel_classes_Resource;
use oat\generis\model\fileReference\ResourceFileSerializer;
use oat\generis\model\GenerisRdf;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\extension\script\ScriptAction;
use oat\oatbox\filesystem\Directory;
use oat\oatbox\filesystem\FileSystemHandler;

/**
 * sudo -u www-data php index.php 'oat\tao\scripts\tools\CleanUpOrphanFiles'
 */
class CleanUpOrphanFiles extends ScriptAction
{

    use OntologyAwareTrait;

    private $wetRun = false;
    private $verbose = false;
    private $removedCount = 0;
    private $redundantCount = 0;
    private $affectedCount = 0;
    private $errorsCount = 0;
    private $report;


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
     * @return Report
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     * @throws \oat\search\base\exception\SearchGateWayExeption
     * @throws \common_exception_Error
     */
    protected function run()
    {
        $this->init();

        $this->report = Report::createInfo('Following files');

        /** @var ResourceFileSerializer $serializer */
        $serializer = $this->getServiceManager()->get(ResourceFileSerializer::SERVICE_ID);

        /** @var ComplexSearchService $search */
        $search = $this->getServiceManager()->get(ComplexSearchService::class);
        $builder = $search->getGateway()->query();

        $list = $search->searchType($builder, GenerisRdf::CLASS_GENERIS_FILE, true);

        $builder->setCriteria($list);

        $resultSet = $search->getGateway()->search($builder);

        $this->report->add(new Report(Report::TYPE_SUCCESS, sprintf('%s Total Files Found in RDS, where: ', $resultSet->total())));

        /** @var core_kernel_classes_Resource $resource */
        foreach ($resultSet as $resource) {
            try {
                $file = $serializer->unserialize($resource);

                if ($this->isRedundant($file)) {
                    $this->manageRedundant($resource, $file);
                    continue;
                }

                $this->manageOrpane($resource, $file);

            } catch (\Exception $exception) {
                $this->errorsCount++;
                $this->report->add(Report::createFailure($exception->getMessage()));
            }
        }

        $this->prepareReport();

        return $this->report;
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
     * @param core_kernel_classes_Resource $resource
     * @return bool
     */
    private function isOrphan(core_kernel_classes_Resource $resource)
    {
        $sql = 'SELECT subject FROM statements s WHERE s.object=?';
        $stmt = $this->getPersistence()->query($sql, [$resource->getUri()]);
        $res = $stmt->fetchAll();

        return 0 === count($res);
    }

    private function getPersistence()
    {
        return $this->getServiceLocator()
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
    protected function remove(core_kernel_classes_Resource $resource)
    {
        if ($this->wetRun) {
            $resource->delete();
            $this->removedCount++;
            $this->getLogger()->info(sprintf('%s has been removed', $resource->getUri()));
        }
    }

    /**
     * @param core_kernel_classes_Resource $resource
     * @param FileSystemHandler $file
     */
    protected function manageRedundant(core_kernel_classes_Resource $resource, FileSystemHandler $file)
    {
        $this->redundantCount++;
        $message = sprintf('resource URI %s : attached file %s', $resource->getUri(), $file->getPrefix());
        if ($this->verbose) {
            $this->report->add(new Report(Report::TYPE_INFO, $message));
        }
        $this->getLogger()->info($message);
        $this->remove($resource);
    }

    /**
     * @param core_kernel_classes_Resource $resource
     * @param FileSystemHandler $file
     */
    protected function manageOrpane(core_kernel_classes_Resource $resource, FileSystemHandler $file)
    {
        $isOrphan = $this->isOrphan($resource);

        if ($isOrphan && !$file->exists()) {
            if ($this->verbose) {
                $this->report->add(new Report(Report::TYPE_INFO, sprintf('URI %s : File %s', $resource->getUri(), $file->getPrefix())));
            }
            $this->remove($resource);
            $this->affectedCount++;
        }
    }

    /**
     * @param $file
     * @return bool
     */
    protected function isRedundant(FileSystemHandler $file)
    {
        $isDirectory = $file instanceof Directory;
        $isRedundant = !$isDirectory && in_array($file->getBasename(), $this->getRedundantFiles());
        return $isRedundant;
    }


    private function prepareReport()
    {
        $this->report->add(new Report(Report::TYPE_SUCCESS, sprintf('%s redundant at RDS', $this->redundantCount)));
        $this->report->add(new Report(Report::TYPE_SUCCESS, sprintf('%s missing at FS', $this->affectedCount)));
        $this->report->add(new Report(Report::TYPE_SUCCESS, sprintf('%s removed at FS', $this->removedCount)));

        if ($this->errorsCount) {
            $this->report->add(new Report(Report::TYPE_ERROR, sprintf('%s errors happened, check details above', $this->errorsCount)));
        }
    }

}