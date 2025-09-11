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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 *
 */

declare(strict_types=1);

namespace oat\tao\test\unit\helpers\form\validators;

use common_Exception;
use PHPUnit\Framework\TestCase;
use tao_helpers_form_elements_xhtml_Textbox;
use tao_helpers_form_Form;
use tao_helpers_form_validators_AnyOf;
use tao_helpers_Uri;

class AnyOfValidatorTest extends TestCase
{
    private const STRING_REFERENCE = 'stringref';
    private const OBJECT_REFERENCE = 'objref';

    /**
     * @var tao_helpers_form_validators_AnyOf
     */
    private $subject;
    /**
     * @dataProvider evaluationValues
     */
    public function testEvaluate(array $options, string $value1, string $value2, $isValid): void
    {
        $subject = $this->createSubject($options, $value2);
        $this->assertEquals($isValid, $subject->evaluate($value1));
    }

    public function testWrongConfiguration(): void
    {
        $this->expectException(common_Exception::class);
        $this->expectExceptionMessage('No reference provided for AnyOf validator');
        $this->createSubject();
    }
    public function evaluationValues()
    {
        return [
            [['reference' => [self::STRING_REFERENCE]], 'a', '', true],
            [['reference' => [self::STRING_REFERENCE]], '', 'b', true],
            [['reference' => [self::STRING_REFERENCE]], 'a', 'b', true],
            [['reference' => [self::STRING_REFERENCE]], '', '', false],
            [
                ['reference' => [$this->createElement('b', tao_helpers_Uri::encode(self::OBJECT_REFERENCE))]],
                'a',
                '',
                true
            ],
        ];
    }

    private function createSubject(array $options = [], $value2 = ''): tao_helpers_form_validators_AnyOf
    {
        $subject = new tao_helpers_form_validators_AnyOf($options);
        $subject->acknowledge($this->createFormMock($value2, $options['reference'][0]));

        return $subject;
    }

    private function createFormMock(
        string $elementValue = '',
        $injectableElement = null
    ): tao_helpers_form_Form {
        $element = is_string($injectableElement) ? $this->createElement($elementValue) : $injectableElement;

        $form = $this->getMockForAbstractClass(tao_helpers_form_Form::class, [], '', false, true, true, ['getElement']);
        $form->method('getElement')->with(tao_helpers_Uri::encode(self::STRING_REFERENCE))->willReturn($element);

        return $form;
    }

    private function createElement(
        string $elementValue,
        string $name = self::STRING_REFERENCE
    ): tao_helpers_form_elements_xhtml_Textbox {
        $element = new tao_helpers_form_elements_xhtml_Textbox($name);
        $element->setValue($elementValue);

        return $element;
    }
}
