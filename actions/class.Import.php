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
 *               2013-2018 (update and modification) Open Assessment Technologies SA;
 */

use oat\oatbox\event\EventManagerAwareTrait;
use oat\tao\model\import\TaskParameterProviderInterface;
use oat\tao\model\task\ImportByHandler;
use oat\tao\model\taskQueue\QueueDispatcher;
use oat\tao\model\taskQueue\TaskLogActionTrait;
use oat\generis\model\OntologyAwareTrait;

/**
 * This controller provide the actions to import resources
 *
 * @author  CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 *
 */
class tao_actions_Import extends tao_actions_CommonModule
{
    /**
     * @var tao_models_classes_import_ImportHandler[]
     */
    private $availableHandlers = [];

    use EventManagerAwareTrait;
    use TaskLogActionTrait;
    use OntologyAwareTrait;
    
    /**
     * initialize the classUri and execute the upload action
     *
     * @requiresRight id WRITE
     */
    public function index()
    {
        $importer = $this->getCurrentImporter();

        $this->propagate($importer);

        $formContainer = new tao_actions_form_Import(
            $importer,
            $this->getAvailableImportHandlers(),
            $this->getCurrentClass()
        );
        $importForm = $formContainer->getForm();

        if ($importForm->isSubmited() && $importForm->isValid()) {
            /** @var QueueDispatcher $queueDispatcher */
            $queueDispatcher = $this->getServiceLocator()->get(QueueDispatcher::SERVICE_ID);

            $task = $queueDispatcher->createTask(
                new ImportByHandler(),
                [
                    ImportByHandler::PARAM_IMPORT_HANDLER => get_class($importer),
                    ImportByHandler::PARAM_FORM_VALUES => $importer instanceof TaskParameterProviderInterface ? $importer->getTaskParameters($importForm) : [],
                    ImportByHandler::PARAM_PARENT_CLASS => $this->getCurrentClass()->getUri()
                ],
                __('Import a %s into "%s"', $importer->getLabel(), $this->getCurrentClass()->getLabel()));

            return $this->returnTaskJson($task);
        }

        $context = Context::getInstance();
        $this->setData('import_extension', $context->getExtensionName());
        $this->setData('import_module', $context->getModuleName());
        $this->setData('import_action', $context->getActionName());

        $this->setData('myForm', $importForm->render());
        $this->setData('formTitle', __('Import '));
        $this->setView('form/import.tpl', 'tao');
    }

    /**
     * Returns the currently selected import handler
     * or the import handler to use by default
     *
     * @return tao_models_classes_import_ImportHandler
     */
    protected function getCurrentImporter()
    {
        $handlers = $this->getAvailableImportHandlers();

        if ($this->hasRequestParameter('importHandler')) {
            foreach ($handlers as $importHandler) {
                if (get_class($importHandler) == $_POST['importHandler']) {
                    return $importHandler;
                }
            }
        }

        $availableImportHandlers = $this->getAvailableImportHandlers();
        $currentImporter = reset($availableImportHandlers);

        return $currentImporter;
    }

    /**
     * Gets the available import handlers for this module
     * Should be overwritten by extensions that want to provide additional ImportHandlers
     *
     * @return tao_models_classes_import_ImportHandler[]
     */
    protected function getAvailableImportHandlers()
    {
        if (empty($this->availableHandlers)) {
            $this->availableHandlers = [
                new tao_models_classes_import_RdfImporter(),
                new tao_models_classes_import_CsvImporter()
            ];
        }

        return $this->availableHandlers;
    }

    /**
     * Helper to get the selected class, needs to be passed as hidden field in the form
     */
    protected function getCurrentClass()
    {
        return $this->getClass(tao_helpers_Uri::decode($this->getRequestParameter('classUri')));
    }

    protected function getValidators()
    {
        return [];
    }
}
