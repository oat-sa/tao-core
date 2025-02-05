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
 * Copyright (c) 2025 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\Translation\Event;

use JsonSerializable;
use oat\oatbox\event\Event;

class TranslationActionEvent implements Event, JsonSerializable
{
    public const ACTION_CREATED = 'translation.created';
    public const ACTION_UPDATED = 'translation.updated';
    public const ACTION_DELETED = 'translation.deleted';

    private string $action;
    private string $type;
    private string $originalResourceId;
    private string $translationResourceId;
    private string $locale;
    private array $data;

    public function __construct(
        string $action,
        string $type,
        string $originalResourceId,
        string $translationResourceId,
        string $locale,
        array $data = []
    ) {
        $this->action = $action;
        $this->type = $type;
        $this->originalResourceId = $originalResourceId;
        $this->translationResourceId = $translationResourceId;
        $this->locale = $locale;
        $this->data = $data;
    }

    public function getName(): string
    {
        return __CLASS__;
    }

    public function jsonSerialize(): array
    {
        return [
            'action' => $this->action,
            'type' => $this->type,
            'originalResourceId' => $this->originalResourceId,
            'translationResourceId' => $this->translationResourceId,
            'locale' => $this->locale,
            'data' => $this->data,
        ];
    }
}
