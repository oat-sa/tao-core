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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 *
 * @author Gabriel Felipe Soares <gabriel.felipe.soares@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\Language;

use oat\tao\model\Language\Language;
use oat\tao\model\Language\LanguageCollection;
use PHPUnit\Framework\TestCase;
use tao_models_classes_LanguageService;

class LanguageCollectionTest extends TestCase
{
    /** @var Language */
    private $language;

    protected function setUp(): void
    {
        $this->language = new Language(
            'uri',
            'pt-BR',
            'Portuguese',
            tao_models_classes_LanguageService::INSTANCE_ORIENTATION_LTR,
            null
        );
    }

    public function testGetters(): void
    {
        $collection = new LanguageCollection();
        $collection->add($this->language);

        $this->assertSame($this->language, $collection->offsetGet(0));
        $this->assertCount(1, $collection);
    }

    public function testJsonSerialize(): void
    {
        $collection = new LanguageCollection();
        $collection->add($this->language);

        $this->assertSame(
            [
                [
                    'uri' => $this->language->getUri(),
                    'code' => $this->language->getCode(),
                    'label' => $this->language->getLabel(),
                    'orientation' => $this->language->getOrientation(),
                ]
            ],
            json_decode(json_encode($collection->jsonSerialize()), true)
        );
    }
}
