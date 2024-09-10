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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\form\Modifier;

use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use tao_helpers_form_Form;

abstract class AbstractFormModifier implements FormModifierInterface
{
    protected Ontology $ontology;

    public function __construct(Ontology $ontology)
    {
        $this->ontology = $ontology;
    }

    protected function getInstance(tao_helpers_form_Form $form, array $options = []): ?core_kernel_classes_Resource
    {
        if (($options[self::OPTION_INSTANCE] ?? null) instanceof core_kernel_classes_Resource) {
            return $options[self::OPTION_INSTANCE];
        }

        $instanceUri = $form->getValue(self::FORM_INSTANCE_URI);

        return $instanceUri ? $this->ontology->getResource($instanceUri) : null;
    }
}