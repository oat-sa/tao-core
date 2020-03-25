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
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the
 * project TAO & TAO2);
 *   2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *   2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *   2013-2018 (update and modification) Open Assessment Technologies SA;
 */

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\log\LoggerAwareTrait;
use oat\tao\model\resources\SecureResourceServiceInterface;
use oat\tao\model\task\ExportByHandler;
use oat\tao\model\taskQueue\QueueDispatcher;
use oat\tao\model\taskQueue\TaskLogActionTrait;

/**
 * This controller provide the actions to export and manage exported data
 */
class tao_actions_Export extends tao_actions_CommonModule
{
    use TaskLogActionTrait;
    use OntologyAwareTrait;
    use LoggerAwareTrait;

    /**
     * @return mixed
     * @throws common_Exception
     */
    public function index()
    {
        $formData = [];

        if ($this->hasRequestParameter('classUri') && trim($this->getRequestParameter('classUri')) !== '') {
            $formData['class'] = $this->getClass(tao_helpers_Uri::decode($this->getRequestParameter('classUri')));
        }

        if (
            $this->hasRequestParameter('uri')
            && $this->hasRequestParameter('classUri')
            && trim($this->getRequestParameter('uri')) !== ''
        ) {
            $formData['instance'] = $this->getResource(tao_helpers_Uri::decode($this->getRequestParameter('uri')));
        }

        $formData['id'] = $this->getRequestParameter('id');

        if (!$this->isExportable($formData)) {
            $this->setData('message', $this->getNotExportableMessage($formData));
            $this->setView('form/export_error_feedback.tpl', 'tao');

            return;
        }

        $handlers = $this->getAvailableExportHandlers();
        $exporter = $this->getCurrentExporter();

        $selectedResource = $formData['instance'] ?? $formData['class'];

        if (!$selectedResource) {
            throw new common_exception_MissingParameter();
        }

        $formFactory = new tao_actions_form_Export($handlers, $exporter->getExportForm($selectedResource), $formData);
        $exportForm = $formFactory->getForm();
        if ($exporter !== null) {
            $exportForm->setValues(['exportHandler' => get_class($exporter)]);
        }
        $this->setData('exportForm', $exportForm->render());

        // if export form submitted
        if ($this->hasRequestParameter('exportChooser_sent') && $this->getRequestParameter('exportChooser_sent') == 1) {
            $exportData = $this->getRequestParameters();

            if (isset($exportData['instances'])) {
                $instances = json_decode(urldecode($exportData['instances']));
                unset($exportData['instances']);

                //allow to export complete classes
                if (isset($exportData['type']) && $exportData['type'] === 'class') {
                    $children = [[]];
                    foreach ($instances as $instance) {
                        $class = $this->getClass(tao_helpers_Uri::decode($instance));
                        $children[] = $class->getInstances();
                    }
                    $exportData['instances'] = array_merge(...$children);
                } else {
                    foreach ($instances as $instance) {
                        $exportData['instances'][] = tao_helpers_Uri::decode($instance);
                    }
                }

                if (!empty($exportData['instances'])) {
                    $this->getSecureResourceService()->validatePermissions($exportData['instances'], ['READ']);
                }
            } elseif (isset($exportData['exportInstance'])) {
                $exportData['exportInstance'] = tao_helpers_Uri::decode($exportData['exportInstance']);

                $this->getSecureResourceService()->validatePermissions([$exportData['exportInstance']], ['READ']);
            }

            /** @var QueueDispatcher $queueDispatcher */
            $queueDispatcher = $this->getServiceLocator()->get(QueueDispatcher::SERVICE_ID);

            $task = $queueDispatcher->createTask(
                new ExportByHandler(),
                [
                    ExportByHandler::PARAM_EXPORT_HANDLER => get_class($exporter),
                    ExportByHandler::PARAM_EXPORT_DATA    => $exportData
                ],
                __('Export "%s" in %s', $selectedResource->getLabel(), $exporter->getLabel())
            );

            return $this->returnTaskJson($task);
        }

        $context = Context::getInstance();
        $this->setData('export_extension', $context->getExtensionName());
        $this->setData('export_module', $context->getModuleName());
        $this->setData('export_action', $context->getActionName());

        $this->setData('formTitle', __('Export '));
        $this->setView('form/export.tpl', 'tao');
    }

    /**
     * Is the metadata of the given resource is exportable?
     *
     * @param array $formData
     *
     * @return bool
     * @author Gyula Szucs, <gyula@taotesting.com>
     */
    protected function isExportable(array $formData): bool
    {
        return true;
    }

    /**
     * Return a message, if the metadata of the resource is not exportable
     *
     * @return string
     * @author Gyula Szucs, <gyula@taotesting.com>
     */
    protected function getNotExportableMessage($formData): string
    {
        return __('Metadata export is not available for the selected resource.');
    }

    protected function getResourcesToExport()
    {
        $returnValue = [];
        if ($this->hasRequestParameter('uri') && trim($this->getRequestParameter('uri')) != '') {
            $returnValue[] = $this->getResource(tao_helpers_Uri::decode($this->getRequestParameter('uri')));
        } elseif ($this->hasRequestParameter('classUri') && trim($this->getRequestParameter('classUri')) != '') {
            $class = $this->getClass(tao_helpers_Uri::decode($this->getRequestParameter('classUri')));
            $resourceService = $this->getSecureResourceService();
            $returnValue = $resourceService->getAllChildren($class);
        } else {
            $this->logWarning('No resources to export');
        }

        return $returnValue;
    }

    private function getSecureResourceService(): SecureResourceServiceInterface
    {
        return $this->getServiceLocator()->get(SecureResourceServiceInterface::SERVICE_ID);
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
            if (
                class_exists($exportHandler)
                && in_array('tao_models_classes_export_ExportHandler', class_implements($exportHandler))
            ) {
                return new $exportHandler();
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
     * @return \tao_models_classes_export_ExportHandler[]
     */
    protected function getAvailableExportHandlers()
    {
        return [
            new tao_models_classes_export_RdfExporter()
        ];
    }
}
