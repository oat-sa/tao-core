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
 * Copyright (c) 2021  (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\helpers\test\unit\helpers\form;

use oat\generis\test\TestCase;
use tao_helpers_form_Form;
use tao_helpers_form_FormElement;
use tao_helpers_form_elements_xhtml_AsyncFile;

class TaoHelpersFormTest extends TestCase
{
    /** @var tao_helpers_form_Form */
    private $subject;

    public function setUp(): void
    {
        $this->subject = $this->getMockForAbstractClass(tao_helpers_form_Form::class);
    }

    public function testFormHasAsyncFileUpload(): void
    {
        $this->subject->addElement($this->createMock(tao_helpers_form_elements_xhtml_AsyncFile::class));

        $this->assertTrue($this->subject->hasAsyncFileUpload($this->subject));
    }

    public function testFormDoesnotHaveAsyncFileUpload(): void
    {
        $this->assertFalse($this->subject->hasAsyncFileUpload($this->subject));
    }
    
    public function testIsFormInstance(): void
    {
        $this->subject->addElement($this->getMockForAbstractClass(tao_helpers_form_FormElement::class));
        $this->subject->getElements()[0]->setName("tao.forms.instance");
        $this->assertTrue($this->subject->isFormInstance($this->subject));
    }

    public function testNotFormInstance(): void
    {
        $this->assertFalse($this->subject->isFormInstance($this->subject));
    }
}
