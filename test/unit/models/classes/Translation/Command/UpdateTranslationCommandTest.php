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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA;
 *
 * @author Gabriel Felipe Soares <gabriel.felipe.soares@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\Translation\Command;

use oat\tao\model\TaoOntology;
use oat\tao\model\Translation\Command\UpdateTranslationCommand;
use PHPUnit\Framework\TestCase;

class UpdateTranslationCommandTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $resourceUri = 'http://example.com/resource/1';

        $command = new UpdateTranslationCommand(
            $resourceUri,
            TaoOntology::PROPERTY_VALUE_TRANSLATION_PROGRESS_TRANSLATED
        );

        $this->assertSame($resourceUri, $command->getResourceUri());
        $this->assertSame(TaoOntology::PROPERTY_VALUE_TRANSLATION_PROGRESS_TRANSLATED, $command->getProgressUri());
    }
}
