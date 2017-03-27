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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model\export;

use oat\oatbox\service\ConfigurableService;
use oat\oatbox\task\Queue;
use tao_helpers_Export;
use tao_helpers_Uri;
use common_report_Report;
use common_exception_UserReadableException;
use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use tao_helpers_report_Rendering;

/**
 * Class ExportService
 *
 * @package oat\tao\model\export
 * @author Antoine Robin, <antoine@taotesting.com>
 */
class ExportService extends ConfigurableService
{

    const SERVICE_ID = 'tao/export';

    const ASYNCHRONOUS_QUEUE = 'asynchronous';

    private $data = [];

    /** @var core_kernel_classes_Resource */
    private $resource = null;

    /**
     * @return bool
     */
    public function isAsynchronous()
    {
        return ($this->hasOption(self::ASYNCHRONOUS_QUEUE) && $this->getOption(self::ASYNCHRONOUS_QUEUE) === true);
    }

    public function export($data, $resource, $context)
    {
        $this->data = $data;
        $this->resource = $resource;

        if($this->isAsynchronous()){
            return $this->asynchronousExport($context);
        } else {
            return $this->synchronousExport();
        }

    }

    protected function asynchronousExport($context)
    {
        $exportLabel = isset($this->data['filename']) ? $this->data['filename'] : $this->resource->getLabel();

        /**
         * @var $taskQueue Queue
         */
        $taskQueue = $this->getServiceManager()->get(Queue::SERVICE_ID);

        $this->data['userUri'] = \common_session_SessionManager::getSession()->getUserUri();
        $this->data['taskLabel'] = __('Export File') . ' ' . $exportLabel;
        $task = $taskQueue->createTask(ExportTask::class , $this->data , false, __('Export File') . ' ' . $exportLabel, $context);

        if($task === false){
            return ['exported' => false, 'message' => __("error occured during export task")];
        }

        return ['exported' => true, 'message' => __("We are preparing your export")];

    }

    protected function synchronousExport()
    {
        if (isset($this->data['instances'])) {
            $instances = json_decode(urldecode($this->data['instances']));
            unset($this->data['instances']);

            foreach ($instances as $instance){
                $this->data['instances'][tao_helpers_Uri::decode($instance)] = tao_helpers_Uri::decode($instance);
            }
        } elseif (isset($this->data['exportInstance'])) {
            $this->data['exportInstance'] = tao_helpers_Uri::decode($this->data['exportInstance']);
        }


        //allow to export complete classes
        if(isset($this->data['classes'])){
            $classes = json_decode(urldecode($this->data['classes']));
            unset($this->data['classes']);


            $children = array();
            foreach ($classes as $classUri){
                $class = new core_kernel_classes_Class(tao_helpers_Uri::decode($classUri));
                $uris = array_keys($class->getInstances());
                $children = array_combine($uris,$uris);
            }

            if(empty($this->data['instances'])){
                $this->data['instances'] = [];
            }
            $this->data['instances'] = array_merge($this->data['instances'],$children);
        }

        $file = null;
        try {
            \common_Logger::d($this->data['exportHandler']);
            $exporter = new $this->data['exportHandler']();
            $report = $exporter->export($this->data, tao_helpers_Export::getExportPath());
            $file = $report;
        } catch (common_exception_UserReadableException $e) {
            $report = common_report_Report::createFailure($e->getUserMessage());
        }

        $html = '';
        if ($report instanceof common_report_Report) {
            $file = $report->getData();
            if ($report->getType() === common_report_Report::TYPE_ERROR || $report->containsError()) {
                $report->setType(common_report_Report::TYPE_ERROR);
                if (! $report->getMessage()) {
                    $report->setMessage(__('Error(s) has occurred during export.'));
                }
                $html = tao_helpers_report_Rendering::render($report);
            }
        }
        if ($html !== '') {
            echo $html;
        } elseif (! is_null($file) && file_exists($file)) {
            setcookie("fileDownload", "true", 0, "/");
            tao_helpers_Export::outputFile(tao_helpers_Export::getRelativPath($file));
            return;
        }
        return;
    }
}