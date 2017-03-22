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
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *               2013-     (update and modification) Open Assessment Technologies SA;
 *
 */


use oat\tao\model\notification\NotificationServiceInterface;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\task\Queue;
use oat\oatbox\filesystem\File;
/**
 * This controller provide the actions to export and manage exported data
 *
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 *
 */
class tao_actions_Export extends tao_actions_CommonModule
{

    use \oat\tao\model\TaskQueueActionTrait;

    /**
     * get the path to save and retrieve the exported files regarding the current extension
     * @return string the path
     */
    protected function getExportPath()
    {
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'export';
        if (!file_exists($path)) {
            mkdir($path);
        }

        return $path;
    }

    /**
     * Does EVERYTHING
     * @todo cleanup interface
     */
    public function index()
    {
        $formData = array();
        if ($this->hasRequestParameter('classUri')) {
            if (trim($this->getRequestParameter('classUri')) != '') {
                $formData['class'] = new core_kernel_classes_Class(tao_helpers_Uri::decode($this->getRequestParameter('classUri')));
            }
        }
        if ($this->hasRequestParameter('uri') && $this->hasRequestParameter('classUri')) {
            if (trim($this->getRequestParameter('uri')) != '') {
                $formData['instance'] = new core_kernel_classes_Resource(tao_helpers_Uri::decode($this->getRequestParameter('uri')));
            }
        }
        $formData['id'] = $this->getRequestParameter('id');

        if (!$this->isExportable($formData)) {
            $this->setData('message', $this->getNotExportableMessage($formData));
            $this->setView('form/export_error_feedback.tpl', 'tao');
            return;
        }

        $handlers = $this->getAvailableExportHandlers();
        $exporter = $this->getCurrentExporter();

        $selectedResource = isset($formData['instance']) ? $formData['instance'] : $formData['class'];
        $formFactory = new tao_actions_form_Export($handlers, $exporter->getExportForm($selectedResource), $formData);
        $myForm = $formFactory->getForm();
        if (!is_null($exporter)) {
            $myForm->setValues(array('exportHandler' => get_class($exporter)));
        }
        $this->setData('myForm', $myForm->render());
        if ($this->hasRequestParameter('exportChooser_sent') && $this->getRequestParameter('exportChooser_sent') == 1) {

            $exportData = $this->getRequestParameters();

            $className = get_class($this);
            $exportData['selfClass'] = $className;
            $exportData['exportHandler'] = get_class($exporter);

            /**
             * @var $taskQueue Queue
             */
            $taskQueue = $this->getServiceManager()->get(Queue::SERVICE_ID);

            $task = $taskQueue->createTask( [$className , 'exportTask'] , $exportData , false, __('Export File'), $this->getContext());

            if($task === false){
                $this->returnJson(['exported' => false, 'message' => __("error occured during export task")]);
                return;
            }

            $this->returnJson(['exported' => true, 'message' => __("Export is in taskQueue")]);
            return;

        }

        $context = Context::getInstance();
        $this->setData('export_extension', $context->getExtensionName());
        $this->setData('export_module', $context->getModuleName());
        $this->setData('export_action', $context->getActionName());

        $this->setData('formTitle', __('Export '));
        $this->setView('form/export.tpl', 'tao');

    }

    protected function getContext()
    {
        return 'Export';
    }

    public static function exportTask($options)
    {

        if(!isset($options['selfClass'])) {
            throw new \common_Exception('Wrong option parameter, selfClass missing');
        }

        if(!isset($options['exportHandler'])) {
            throw new \common_Exception('Wrong option parameter, exporter missing');
        }

        $className =  $options['selfClass'] ;
        $controller = new $className();

        $exporterClassName =  $options['exportHandler'] ;
        $exporter = new $exporterClassName();



        if (isset($options['instances'])) {
            $instances = json_decode(urldecode($options['instances']));
            unset($options['instances']);

            //allow to export complete classes

            if(isset($options['type']) && $options['type'] === 'class'){

                $children = array();
                foreach ($instances as $instance){
                    $class = new core_kernel_classes_Class(tao_helpers_Uri::decode($instance));
                    $children = array_keys($class->getInstances());
                }
                $options['instances'] = $children;
            } else {
                foreach ($instances as $instance){
                    $options['instances'][] = tao_helpers_Uri::decode($instance);
                }
            }

        } elseif (isset($options['exportInstance'])) {
            $options['exportInstance'] = tao_helpers_Uri::decode($options['exportInstance']);
        }

        $file = null;

        try {
            $report = $exporter->export($options, tao_helpers_Export::getExportPath());
            $file = $report->getData();

        } catch (common_exception_UserReadableException $e) {
            $report = common_report_Report::createFailure($e->getUserMessage());
        }
        /** @var NotificationServiceInterface $notificationService */

        if ($report instanceof common_report_Report) {


            if ($report->getType() === common_report_Report::TYPE_ERROR || $report->containsError()) {
                $report->setType(common_report_Report::TYPE_ERROR);
                if (! $report->getMessage()) {
                    $report->setMessage(__('Error(s) has occurred during export.'));
                }

            } else {

                /**
                 * @var $fileSystem FileSystemService
                 */
                $fileSystem = $controller->getServiceManager()->get(FileSystemService::SERVICE_ID);
                $fs = $fileSystem->getFileSystem('taskQueueStorage');


                $fs->put(basename($file), file_get_contents($file));
                $report->setData(basename($file));
            }
        }

        return $report;
    }

    /**
     * Is the metadata of the given resource is exportable?
     *
     * @author Gyula Szucs, <gyula@taotesting.com>
     * @param array $formData
     * @return bool
     */
    protected function isExportable(array $formData)
    {
        return true;
    }

    /**
     * Return a message, if the metadata of the resource is not exportable
     *
     * @author Gyula Szucs, <gyula@taotesting.com>
     * @return string
     */
    protected function getNotExportableMessage($formData)
    {
        return __('Metadata export is not available for the selected resource.');
    }

    protected function getResourcesToExport()
    {
        $returnValue = array();
        if ($this->hasRequestParameter('uri') && trim($this->getRequestParameter('uri')) != '') {
            $returnValue[] = new core_kernel_classes_Resource(tao_helpers_Uri::decode($this->getRequestParameter('uri')));
        } elseif ($this->hasRequestParameter('classUri') && trim($this->getRequestParameter('classUri')) != '') {
            $class = new core_kernel_classes_Class(tao_helpers_Uri::decode($this->getRequestParameter('classUri')));
            $returnValue = $class->getInstances(true);
        } else {
            common_Logger::w('No resources to export');
        }

        return $returnValue;
    }

    /**
     * Returns the selected ExportHandler
     *
     * @return tao_models_classes_export_ExportHandler
     * @throws common_Exception
     */
    private function getCurrentExporter()
    {
        if ($this->hasRequestParameter('exportHandler')) {
            $exportHandler = $_REQUEST['exportHandler'];//allow method "GET"
            if (class_exists($exportHandler) && in_array('tao_models_classes_export_ExportHandler',
                    class_implements($exportHandler))
            ) {
                $exporter = new $exportHandler();

                return $exporter;
            } else {
                throw new common_Exception('Unknown or incompatible ExporterHandler: \'' . $exportHandler . '\'');
            }
        } else {
            return current($this->getAvailableExportHandlers());
        }
    }

    /**
     * Override this function to add your own custom ExportHandlers
     *
     * @return array an array of ExportHandlers
     */
    protected function getAvailableExportHandlers()
    {
        return array(
            new tao_models_classes_export_RdfExporter()
        );
    }


    /**
     * @deprecated
     */
    protected function sendFileToClient($file, $test)
    {

        throw new common_exception_DeprecatedApiMethod('Please stop using this method');
    }

    public function outputFile()
    {

        if($this->hasRequestParameter('taskId')){
            /**
             * @var $task \oat\Taskqueue\JsonTask
             */
            $task   = $this->getTask($this->getRequestParameter('taskId'));
            $report = $task->getReport();
            $report = \common_report_Report::jsonUnserialize($report);
            $filename = '';

            if(!is_null($report)){
                /** @var \common_report_Report $success */
                foreach ($report->getSuccesses() as $success){
                    if(!is_null($filename = $success->getData())){
                        break;
                    }
                }
                /** @var FileSystemService $fileSystem */
                $fileSystem = $this->getServiceManager()->get(FileSystemService::SERVICE_ID);
                $directory = $fileSystem->getDirectory('taskQueueStorage');
                $file = $directory->getFile($filename);
                $this->output($file);
                return;
            }
        }
        echo 'NON';
        return;

    }

    protected function output(File $file)
    {

        $tmpFile = \tao_helpers_Export::getExportPath() . DIRECTORY_SEPARATOR . $file->getBasename();
        if (($resource = fopen($tmpFile, 'w')) === false) {
            throw new \common_Exception('Unable to write "' . $file->getPrefix() . '" into tmp folder("' . $tmpFile . '").');
        }

        stream_copy_to_stream($file->readStream(), $resource);
        fclose($resource);

        \tao_helpers_Export::outputFile($file->getBasename());

        return;
    }
}
