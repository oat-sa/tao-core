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

namespace oat\tao\helpers\test\unit\helpers\form\Specification;

use PHPUnit\Framework\TestCase;
use oat\tao\helpers\form\elements\xhtml\SearchDropdown;
use oat\tao\helpers\form\Specification\DependencyPropertyWidgetSpecification;
use tao_helpers_form_elements_Combobox;

class DependencyPropertyWidgetSpecificationTest extends TestCase
{
    /** @var DependencyPropertyWidgetSpecification */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new DependencyPropertyWidgetSpecification();
    }

    /**
     * @dataProvider isSatisfiedByProvider
     */
    public function testisSatisfiedBy(
        bool $expected,
        string $targetWidgetUri,
        string $selectedWidgetUri,
        string $previewsWidget = null
    ): void {
        $this->assertEquals(
            $expected,
            $this->sut->isSatisfiedBy($targetWidgetUri, $selectedWidgetUri, $previewsWidget)
        );
    }

    public function isSatisfiedByProvider(): array
    {
        return [
            'Keep Multiple Choice' => [
                'expected' => true,
                'targetWidgetUri' => 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#SearchTextBox',
                'selectedWidgetUri' => 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#SearchTextBox',
                'previewsWidget' => 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#SearchTextBox',
            ],
            'New Multiple Choice' => [
                'expected' => true,
                'targetWidgetUri' => 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#SearchTextBox',
                'selectedWidgetUri' => 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#SearchTextBox',
                'previewsWidget' => null,
            ],
            'Multiple Choice to Single Choice Combobox' => [
                'expected' => false,
                'targetWidgetUri' => tao_helpers_form_elements_Combobox::WIDGET_ID,
                'selectedWidgetUri' => tao_helpers_form_elements_Combobox::WIDGET_ID,
                'previewsWidget' => 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#SearchTextBox',
            ],
            'Multiple Choice to Single Choice Dropdown' => [
                'expected' => false,
                'targetWidgetUri' => SearchDropdown::WIDGET_ID,
                'selectedWidgetUri' => SearchDropdown::WIDGET_ID,
                'previewsWidget' => 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#SearchTextBox',
            ],
            'Single Choice Dropdown to Single Choice Combobox' => [
                'expected' => true,
                'targetWidgetUri' => tao_helpers_form_elements_Combobox::WIDGET_ID,
                'selectedWidgetUri' => tao_helpers_form_elements_Combobox::WIDGET_ID,
                'previewsWidget' => SearchDropdown::WIDGET_ID,
            ],
            'Single Choice Combobox to Single Choice Dropdown' => [
                'expected' => true,
                'targetWidgetUri' => SearchDropdown::WIDGET_ID,
                'selectedWidgetUri' => SearchDropdown::WIDGET_ID,
                'previewsWidget' => tao_helpers_form_elements_Combobox::WIDGET_ID,
            ],
            'Single Choice Combobox to Multiple Choice' => [
                'expected' => false,
                'targetWidgetUri' => 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#SearchTextBox',
                'selectedWidgetUri' => 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#SearchTextBox',
                'previewsWidget' => tao_helpers_form_elements_Combobox::WIDGET_ID,
            ],
        ];
    }
}
