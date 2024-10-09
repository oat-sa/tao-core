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
 */

declare(strict_types=1);

namespace oat\tao\model\Translation\Command;

use oat\tao\model\TaoOntology;
use oat\tao\model\Translation\Exception\ResourceTranslationException;

class UpdateTranslationCommand
{
    private string $resourceUri;
    private string $progressUri;

    public function __construct(string $resourceUri, string $progressUri)
    {
        if (
            !in_array(
                $progressUri,
                [
                    TaoOntology::PROPERTY_VALUE_TRANSLATION_PROGRESS_PENDING,
                    TaoOntology::PROPERTY_VALUE_TRANSLATION_PROGRESS_TRANSLATING,
                    TaoOntology::PROPERTY_VALUE_TRANSLATION_PROGRESS_TRANSLATED,
                ],
                true
            )
        ) {
            throw new ResourceTranslationException(sprintf('Translation status %s is not allowed', $progressUri));
        }

        $this->resourceUri = $resourceUri;
        $this->progressUri = $progressUri;
    }

    public function getResourceUri(): string
    {
        return $this->resourceUri;
    }

    public function getProgressUri(): string
    {
        return $this->progressUri;
    }
}
