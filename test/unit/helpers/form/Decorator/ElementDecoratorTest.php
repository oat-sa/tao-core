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

namespace oat\tao\helpers\test\unit\helpers\form\Decorator;

use oat\generis\model\data\Ontology;
use oat\generis\test\TestCase;
use oat\tao\helpers\form\Decorator\ElementDecorator;
use PHPUnit\Framework\MockObject\MockObject;
use tao_helpers_form_Form;
use tao_helpers_form_FormElement;

class ElementDecoratorTest extends TestCase
{
    /** @var ElementDecorator */
    private $sut;

    /** @var MockObject|string */
    private $ontology;

    /** @var MockObject|tao_helpers_form_Form */
    private $form;

    /** @var MockObject|tao_helpers_form_FormElement */
    private $element;

    public function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::SERVICE_ID);
        $this->form = $this->createMock(tao_helpers_form_Form::class);
        $this->element = $this->createMock(tao_helpers_form_FormElement::class);

        $this->sut = new ElementDecorator(
            $this->ontology,
            $this->form,
            $this->element
        );
    }

    public function testGetters(): void
    {
        $this->markTestIncomplete('TODO');
    }
}
