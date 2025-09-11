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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\test\unit\helpers\form\Feeder;

use RuntimeException;
use tao_helpers_form_Form;
use PHPUnit\Framework\TestCase;
use tao_helpers_form_Validator;
use tao_helpers_form_FormElement;
use tao_helpers_form_elements_Textbox;
use tao_helpers_form_elements_Textarea;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\helpers\form\Feeder\SanitizerValidationFeeder;

class SanitizerValidationFeederTest extends TestCase
{
    /** @var SanitizerValidationFeeder */
    private $sut;

    /** @var MockObject|tao_helpers_form_FormElement */
    private $element1;

    /** @var MockObject|tao_helpers_form_FormElement */
    private $element2;

    /** @var MockObject|tao_helpers_form_Validator */
    private $validator;

    /** @var MockObject|tao_helpers_form_Form */
    private $form;

    protected function setUp(): void
    {
        $this->element1 = $this->createMock(tao_helpers_form_FormElement::class);
        $this->element2 = $this->createMock(tao_helpers_form_FormElement::class);
        $this->validator = $this->createMock(tao_helpers_form_Validator::class);
        $this->form = $this->createMock(tao_helpers_form_Form::class);

        $this->sut = new SanitizerValidationFeeder();
    }

    public function testFeed(): void
    {
        $this->element1
            ->expects($this->once())
            ->method('getWidget')
            ->willReturn(tao_helpers_form_elements_Textbox::WIDGET_ID);
        $this->element1
            ->expects($this->once())
            ->method('getName')
            ->willReturn('element1Name');
        $this->element1
            ->expects($this->once())
            ->method('getInputValue')
            ->willReturn('element1InputValue');
        $this->element1
            ->expects($this->never())
            ->method('getRawValue');
        $this->element1
            ->expects($this->once())
            ->method('addValidator')
            ->with($this->validator);

        $this->element2
            ->expects($this->once())
            ->method('getWidget')
            ->willReturn(tao_helpers_form_elements_Textarea::WIDGET_ID);
        $this->element2
            ->expects($this->once())
            ->method('getInputValue')
            ->willReturn(null);
        $this->element2
            ->expects($this->once())
            ->method('getRawValue')
            ->willReturn('element2RawValue');
        $this->element2
            ->expects($this->once())
            ->method('addValidator')
            ->with($this->validator);

        $this->form
            ->expects($this->once())
            ->method('hasElement')
            ->with('element1Name')
            ->willReturn(true);
        $this->form
            ->expects($this->once())
            ->method('getElement')
            ->with('element2Name')
            ->willReturn($this->element2);

        $this->sut
            ->setForm($this->form)
            ->addValidator($this->validator)
            ->addElement($this->element1)
            ->addElementByUri('element2Name')
            ->feed();
    }

    public function testAddElementWithoutForm(): void
    {
        $this->expectException(RuntimeException::class);

        $this->sut->addElement($this->element1);
    }

    public function testAddElementByUriWithoutForm(): void
    {
        $this->expectException(RuntimeException::class);

        $this->sut->addElementByUri('element1Name');
    }

    public function testFeedWithoutValidator(): void
    {
        $this->element1
            ->expects($this->once())
            ->method('getWidget')
            ->willReturn(tao_helpers_form_elements_Textbox::WIDGET_ID);
        $this->element1
            ->expects($this->once())
            ->method('getName')
            ->willReturn('element1Name');
        $this->element1
            ->expects($this->never())
            ->method('getInputValue');
        $this->element1
            ->expects($this->never())
            ->method('getRawValue');
        $this->element1
            ->expects($this->never())
            ->method('addValidator');

        $this->element2
            ->expects($this->once())
            ->method('getWidget')
            ->willReturn(tao_helpers_form_elements_Textarea::WIDGET_ID);
        $this->element2
            ->expects($this->never())
            ->method('getInputValue');
        $this->element2
            ->expects($this->never())
            ->method('getRawValue');
        $this->element2
            ->expects($this->never())
            ->method('addValidator');

        $this->form
            ->expects($this->once())
            ->method('hasElement')
            ->with('element1Name')
            ->willReturn(true);
        $this->form
            ->expects($this->once())
            ->method('getElement')
            ->with('element2Name')
            ->willReturn($this->element2);

        $this->sut
            ->setForm($this->form)
            ->addElement($this->element1)
            ->addElementByUri('element2Name')
            ->feed();
    }

    public function testFeedWithInvalidWidget(): void
    {
        $this->element1
            ->expects($this->once())
            ->method('getWidget')
            ->willReturn('invalidWidget1');
        $this->element1
            ->expects($this->never())
            ->method('getName');
        $this->element1
            ->expects($this->never())
            ->method('getInputValue');
        $this->element1
            ->expects($this->never())
            ->method('getRawValue');
        $this->element1
            ->expects($this->never())
            ->method('addValidator');

        $this->element2
            ->expects($this->once())
            ->method('getWidget')
            ->willReturn('invalidWidget2');
        $this->element2
            ->expects($this->never())
            ->method('getInputValue');
        $this->element2
            ->expects($this->never())
            ->method('getRawValue');
        $this->element2
            ->expects($this->never())
            ->method('addValidator');

        $this->form
            ->expects($this->never())
            ->method('hasElement');
        $this->form
            ->expects($this->once())
            ->method('getElement')
            ->with('element2Name')
            ->willReturn($this->element2);

        $this->sut
            ->setForm($this->form)
            ->addValidator($this->validator)
            ->addElement($this->element1)
            ->addElementByUri('element2Name')
            ->feed();
    }

    public function testFeedWithNullOrEmptyValue(): void
    {
        $this->element1
            ->expects($this->once())
            ->method('getWidget')
            ->willReturn(tao_helpers_form_elements_Textbox::WIDGET_ID);
        $this->element1
            ->expects($this->once())
            ->method('getName')
            ->willReturn('element1Name');
        $this->element1
            ->expects($this->once())
            ->method('getInputValue')
            ->willReturn('');
        $this->element1
            ->expects($this->never())
            ->method('getRawValue');
        $this->element1
            ->expects($this->never())
            ->method('addValidator');

        $this->element2
            ->expects($this->once())
            ->method('getWidget')
            ->willReturn(tao_helpers_form_elements_Textarea::WIDGET_ID);
        $this->element2
            ->expects($this->once())
            ->method('getInputValue')
            ->willReturn(null);
        $this->element2
            ->expects($this->once())
            ->method('getRawValue')
            ->willReturn(null);
        $this->element2
            ->expects($this->never())
            ->method('addValidator');

        $this->form
            ->expects($this->once())
            ->method('hasElement')
            ->with('element1Name')
            ->willReturn(true);
        $this->form
            ->expects($this->once())
            ->method('getElement')
            ->with('element2Name')
            ->willReturn($this->element2);

        $this->sut
            ->setForm($this->form)
            ->addValidator($this->validator)
            ->addElement($this->element1)
            ->addElementByUri('element2Name')
            ->feed();
    }

    public function testFeedWithInvalidElements(): void
    {
        $this->element1
            ->expects($this->once())
            ->method('getWidget')
            ->willReturn(tao_helpers_form_elements_Textbox::WIDGET_ID);
        $this->element1
            ->expects($this->once())
            ->method('getName')
            ->willReturn('invalidElement1Name');
        $this->element1
            ->expects($this->never())
            ->method('getInputValue');
        $this->element1
            ->expects($this->never())
            ->method('getRawValue');
        $this->element1
            ->expects($this->never())
            ->method('addValidator');

        $this->element2
            ->expects($this->never())
            ->method('getName')
            ->willReturn('element2Name');
        $this->element2
            ->expects($this->never())
            ->method('getWidget');
        $this->element2
            ->expects($this->never())
            ->method('getInputValue');
        $this->element2
            ->expects($this->never())
            ->method('getRawValue');
        $this->element2
            ->expects($this->never())
            ->method('addValidator');

        $this->form
            ->expects($this->once())
            ->method('hasElement')
            ->with('invalidElement1Name')
            ->willReturn(false);

        $this->form
            ->expects($this->once())
            ->method('getElement')
            ->with('invalidElement2Name')
            ->willReturn(null);

        $this->sut
            ->setForm($this->form)
            ->addValidator($this->validator)
            ->addElement($this->element1)
            ->addElementByUri('invalidElement2Name')
            ->feed();
    }
}
