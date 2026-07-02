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
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\test\unit\helpers\form\elements\xhtml;

use oat\oatbox\service\ServiceManager;
use oat\tao\model\service\ApplicationService;
use PHPUnit\Framework\TestCase;
use tao_helpers_form_elements_xhtml_Button;

class RubyTagsFormRenderingTest extends TestCase
{
    private ?ServiceManager $previousServiceManager = null;
    protected function setUp(): void
    {
        parent::setUp();
        $this->previousServiceManager = ServiceManager::getServiceManager();

        $config = new \common_persistence_KeyValuePersistence(new \common_persistence_InMemoryKvDriver(), []);
        $config->set(ApplicationService::SERVICE_ID, $this->createApplicationServiceMock());

        ServiceManager::setServiceManager(new ServiceManager($config));
    }

    protected function tearDown(): void
    {
        if ($this->previousServiceManager !== null) {
            ServiceManager::setServiceManager($this->previousServiceManager);
        }
        parent::tearDown();
    }

    public function testButtonRenderPreservesRubyHtmlInButtonBody(): void
    {
        $rubyValue = '<ruby>保存</ruby>';
        $button = $this->createButton($rubyValue);
        $button->setAttribute('noLabel', true);

        $html = $button->render();

        $this->assertStringContainsString('>' . $rubyValue . '</button>', $html);
    }

    public function testButtonRenderEscapesRubyHtmlInValueAttribute(): void
    {
        $rubyValue = '<ruby>保存</ruby>';
        $button = $this->createButton($rubyValue);
        $button->setAttribute('noLabel', true);

        $html = $button->render();

        $this->assertStringContainsString('value="&lt;ruby&gt;保存&lt;/ruby&gt;"', $html);
    }

    public function testButtonRenderConvertsRubyPlaceholdersInButtonBody(): void
    {
        $button = $this->createButton('{ruby}漢{/ruby}');
        $button->setAttribute('noLabel', true);

        $html = $button->render();

        $this->assertStringContainsString('><ruby>漢</ruby></button>', $html);
    }

    public function testXhtmlRenderingTraitPreservesRubyHtmlInFormDescLabel(): void
    {
        $rubyDescription = '<ruby>少<rt>すく</rt></ruby>選択';
        $textbox = new \tao_helpers_form_elements_xhtml_Textbox('translation_status');
        $textbox->setDescription($rubyDescription);

        $html = $textbox->renderLabel();

        $this->assertStringContainsString("class='form_desc'", $html);
        $this->assertStringContainsString($rubyDescription, $html);
        $this->assertStringNotContainsString('&lt;ruby&gt;', $html);
    }

    public function testXhtmlRenderingTraitConvertsRubyPlaceholdersInFormDescLabel(): void
    {
        $textbox = new \tao_helpers_form_elements_xhtml_Textbox('field');
        $textbox->setDescription('{ruby}字{/ruby}');

        $html = $textbox->renderLabel();

        $this->assertStringContainsString('<ruby>字</ruby>', $html);
    }

    public function testLabelInfoBoxPreservesRubyHtmlInValue(): void
    {
        $label = new \tao_helpers_form_elements_xhtml_Label('info');
        $label->setValue('結果処理を行いません。 <ruby><rb>6</rb><rt>7</rt></ruby>');

        $html = $label->render();

        $this->assertStringContainsString('form-elt-info', $html);
        $this->assertStringContainsString('<ruby><rb>6</rb><rt>7</rt></ruby>', $html);
        $this->assertStringNotContainsString('&lt;ruby&gt;', $html);
    }

    public function testComboboxOptionUsesPlainTextWithoutRubyTags(): void
    {
        $combobox = new \tao_helpers_form_elements_xhtml_Combobox('outcomeProcessing');
        $combobox->setAttribute('noLabel', true);
        $combobox->setOptions([
            'none' => 'なし {ruby}{rb}6{/rb}{rt}7{/rt}{/ruby}',
        ]);

        $html = $combobox->render();

        $this->assertStringContainsString('>なし 6</option>', $html);
        $this->assertStringNotContainsString('<ruby>', $html);
        $this->assertStringNotContainsString('{ruby}', $html);
    }

    private function createButton(string $value): tao_helpers_form_elements_xhtml_Button
    {
        $button = new tao_helpers_form_elements_xhtml_Button('submit');
        $button->setValue($value);

        return $button;
    }

    private function createApplicationServiceMock(): ApplicationService
    {
        $applicationServiceMock = $this->createMock(ApplicationService::class);
        $applicationServiceMock
            ->method('getDefaultEncoding')
            ->willReturn('UTF-8');

        return $applicationServiceMock;
    }
}
