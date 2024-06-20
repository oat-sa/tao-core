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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\import;

use oat\tao\model\import\Form\RdfImportForm;
use oat\tao\model\Parser\Parser;
use oat\tao\model\upload\UploadService;
use tao_helpers_form_Form as Form;
use tao_models_classes_import_RdfImporter as LegacyRdfImporter;


class RdfImporter extends LegacyRdfImporter
{
    private Parser $parser;
    private UploadService $uploadService;

    public function __construct(Parser $parser, UploadService $uploadService)
    {
        $this->parser = $parser;
        $this->uploadService = $uploadService;
    }

    public function getForm(): Form
    {
        $form = new RdfImportForm();
        return $form->getForm();
    }

    public function import($class, $form, $userId = null)
    {
        $this->uploadService->fetchUploadedFile($form);
        $this->parser->setSource(
            $this->uploadService->fetchUploadedFile($form)
        );

        return parent::import($class, $form, $userId);
    }
}
