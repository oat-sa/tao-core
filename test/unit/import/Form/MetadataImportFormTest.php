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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\test\unit\import\Form;

use oat\tao\model\import\Form\MetadataImportForm;
use oat\generis\test\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use tao_helpers_form_FormElement;

class MetadataImportFormTest extends TestCase
{
    /** @var MetadataImportForm */
    private $subject;

    /** @var MockObject|tao_helpers_form_FormElement */
    private $fileUploadElement;

    /** @var MockObject|tao_helpers_form_FormElement */
    private $hiddenImportElement;

    /** @var MockObject|tao_helpers_form_FormElement */
    private $submitElement;

    public function setUp(): void
    {
        $this->fileUploadElement = $this->createMock(tao_helpers_form_FormElement::class);
        $this->hiddenImportElement = $this->createMock(tao_helpers_form_FormElement::class);
        $this->submitElement = $this->createMock(tao_helpers_form_FormElement::class);

        $this->fileUploadElement
            ->method('getName')
            ->willReturn('fileUploadElement');

        $this->hiddenImportElement
            ->method('getName')
            ->willReturn('hiddenImportElement');

        $this->submitElement
            ->method('getName')
            ->willReturn('submitElement');

        $this->subject = new MetadataImportForm(
            [],
            [],
            $this->fileUploadElement,
            $this->hiddenImportElement,
            $this->submitElement
        );
    }

    public function testConstructWillCallInitFormAndInitElements(): void
    {
        $elements = $this->subject->getForm()->getElements();
        $actions = $this->subject->getForm()->getActions();

        $this->assertCount(2, $elements);
        $this->assertCount(1, $actions);

        $this->assertSame('fileUploadElement', $elements[0]->getName());
        $this->assertSame('hiddenImportElement', $elements[1]->getName());
        $this->assertSame('submitElement', $actions[0]->getName());
    }
}
