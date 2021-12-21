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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

use oat\tao\model\import\Factory\ImportFormFactory;
use oat\tao\model\import\service\AggregatedImportHandler;
use oat\tao\model\routing\Contract\ActionInterface;
use oat\tao\model\TaoOntology;
use oat\tao\model\task\ImportByHandler;
use oat\tao\model\taskQueue\QueueDispatcher;
use oat\tao\model\taskQueue\Service\TaskJsonReporter;
use Psr\Http\Message\ResponseInterface;

class tao_actions_MetadataImport implements ActionInterface
{
    /** @var ImportFormFactory */
    private $formFactory;

    /** @var ResponseInterface */
    private $response;

    /** @var tao_models_classes_import_CsvImporter */
    private $csvImporter;

    /** @var QueueDispatcher */
    private $queueDispatcher;

    /** @var TaskJsonReporter */
    private $taskJsonReporter;

    public function __construct(
        ImportFormFactory $formFactory,
        AggregatedImportHandler $csvImporter,
        ResponseInterface $response,
        QueueDispatcher $queueDispatcher,
        TaskJsonReporter $taskJsonReporter
    ) {
        $this->formFactory = $formFactory;
        $this->response = $response;
        $this->csvImporter = $csvImporter;
        $this->queueDispatcher = $queueDispatcher;
        $this->taskJsonReporter = $taskJsonReporter;
    }

    public function index(): ResponseInterface
    {
        $response = $this->response;
        $form = $this->formFactory
            ->addHandler($this->csvImporter)
            ->create(
                [
                    ImportFormFactory::PARAM_TITLE => __('Import  statistical analysis metadata')
                ]
            );

        if ($form->isSubmited() && $form->isValid()) {
            $task = $this->queueDispatcher->createTask(
                new ImportByHandler(),
                [
                    ImportByHandler::PARAM_IMPORT_HANDLER_SERVICE_ID => get_class($this->csvImporter),
                    ImportByHandler::PARAM_FORM_VALUES => $this->csvImporter->getTaskParameters($form),
                    ImportByHandler::PARAM_PARENT_CLASS => TaoOntology::CLASS_URI_ITEM,
                    //\common_session_SessionManager::getSession()->getUser()->getIdentifier(),
                    ImportByHandler::PARAM_OWNER => null
                ],
                __('Import %s"', $this->csvImporter->getLabel())
            );

            $response->getBody()->write(
                json_encode(
                    [
                        'success' => true,
                        'data' => $this->taskJsonReporter->report($task)
                    ]
                )
            );

            return $response;
        }

        $response->getBody()->write($this->formFactory->getRenderer()->render());

        return $response;
    }
}
