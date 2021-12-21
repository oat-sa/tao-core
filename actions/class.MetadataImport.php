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

use oat\tao\model\import\Form\MetadataImportForm;
use oat\tao\model\import\service\AgnosticImportHandler;
use oat\tao\model\TaoOntology;

class tao_actions_MetadataImport extends tao_actions_Import
{
    protected function getFormTitle(): string
    {
        return __('Import statistical analysis metadata');
    }

    protected function getAvailableImportHandlers()
    {
        return [
            $this->getAgnosticImportHandler()
                ->withLabel(__('CSV file'))
                ->withForm((new MetadataImportForm())->getForm())
        ];
    }

    protected function getImportHandlerServiceIdMap(): array
    {
        return [
            AgnosticImportHandler::class => AgnosticImportHandler::class,
        ];
    }

    protected function getCurrentClass(): core_kernel_classes_Class
    {
        return $this->getClass(TaoOntology::CLASS_URI_ITEM);
    }

    private function getAgnosticImportHandler(): AgnosticImportHandler
    {
        return $this->getPsrContainer()->get(AgnosticImportHandler::class);
    }
}
