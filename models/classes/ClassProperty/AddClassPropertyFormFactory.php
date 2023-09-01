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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\ClassProperty;

use tao_helpers_form_Form as Form;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use Psr\Http\Message\ServerRequestInterface;
use tao_helpers_form_FormContainer as FormContainer;
use tao_actions_form_SimpleProperty as SimpleProperty;
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;

class AddClassPropertyFormFactory extends ConfigurableService
{
    use OntologyAwareTrait;

    public function add(ServerRequestInterface $request, bool $hasWriteAccess): ?Form
    {
        $parsedBody = $request->getParsedBody();

        $class = $this->getClass($parsedBody['id']);
        $index = ($parsedBody['index'] ?? count($class->getProperties(false))) + 1;

        $options = [
            'index' => $index,
            'disableIndexChanges' => $this->isAdvancedSearchEnabled(),
            FormContainer::IS_DISABLED => !$hasWriteAccess,
        ];

        $newProperty = $class->createProperty('Property_' . $index);

        $propFormContainer = new SimpleProperty($class, $newProperty, $options);

        return $propFormContainer->getForm();
    }

    private function isAdvancedSearchEnabled(): bool
    {
        /** @var AdvancedSearchChecker $advancedSearchChecker */
        $advancedSearchChecker = $this->getServiceLocator()->get(AdvancedSearchChecker::class);

        return $advancedSearchChecker->isEnabled();
    }
}
