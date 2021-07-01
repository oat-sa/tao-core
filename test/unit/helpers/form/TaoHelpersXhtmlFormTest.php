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
use tao_helpers_form_xhtml_Form;

class TaoHelpersXhtmlFormTest extends TestCase
{
    /** @var tao_helpers_form_xhtml_Form */
    private $subject;

    public function setUp(): void
    {
        $this->subject = new tao_helpers_form_xhtml_Form();
        $this->tao_helpers_form_xhtml_Form_obj = $this->createMock(tao_helpers_form_xhtml_Form::class);
    }

    public function testHasAsyncFileUpload(): void
    {
        $response = $this->subject->hasAsyncFileUpload($this->tao_helpers_form_xhtml_Form_obj);
        $this->assertIsBool($response);
    }

    public function testIsFormInstance(): void
    {
        $response = $this->subject->isFormInstance($this->tao_helpers_form_xhtml_Form_obj);
        $this->assertIsBool($response);
    }
}
