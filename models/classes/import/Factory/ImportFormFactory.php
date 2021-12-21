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
 * Copyright (c) 2021 (update and modification) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\import\Factory;

use Context;
use oat\generis\model\data\Ontology;
use oat\tao\model\TaoOntology;
use Psr\Http\Message\ServerRequestInterface;
use Renderer;
use tao_actions_form_Import;
use tao_helpers_form_Form;
use tao_models_classes_import_ImportHandler;

class ImportFormFactory
{
    public const PARAM_TITLE = 'title';

    /** @var tao_models_classes_import_ImportHandler[] */
    private $availableHandlers = [];

    /** @var ServerRequestInterface */
    private $request;

    /** @var Renderer */
    private $renderer;

    /** @var Ontology */
    private $ontology;

    public function __construct(ServerRequestInterface $request, Ontology $ontology, Renderer $renderer)
    {
        $this->request = $request;
        $this->renderer = $renderer;
        $this->ontology = $ontology;
    }

    public function addHandler(tao_models_classes_import_ImportHandler $handler): self
    {
        $this->availableHandlers[get_class($handler)] = $handler;

        return $this;
    }

    public function create(array $params): tao_helpers_form_Form
    {
        $importer = $this->getCurrentImporter();

        $formContainer = new tao_actions_form_Import(
            $importer,
            $this->availableHandlers,
            $this->getCurrentClass()
        );

        $importForm = $formContainer->getForm();

        $context = Context::getInstance();
        $this->renderer->setData('import_extension', $context->getExtensionName());
        $this->renderer->setData('import_module', $context->getModuleName());
        $this->renderer->setData('import_action', $context->getActionName());

        $this->renderer->setData('myForm', $importForm->render());
        $this->renderer->setData('formTitle', __($params[self::PARAM_TITLE] ?? 'Import '));
        $this->renderer->setTemplate(__DIR__ . '/../../../../views/templates/form/import.tpl');

        return $importForm;
    }

    public function getRenderer(): Renderer
    {
        return $this->renderer;
    }

    private function getCurrentImporter(): tao_models_classes_import_ImportHandler
    {
        $serverParams = $this->request->getServerParams();

        if (isset($serverParams['importHandler'])) {
            foreach ($this->availableHandlers as $importHandler) {
                if (get_class($importHandler) == $serverParams['importHandler']) {
                    return $importHandler;
                }
            }
        }

        return reset($this->availableHandlers);
    }

    private function getCurrentClass()
    {
        return $this->ontology->getClass(TaoOntology::CLASS_URI_ITEM);
    }
}
