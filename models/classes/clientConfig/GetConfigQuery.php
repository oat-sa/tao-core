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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA.
 *
 * @author Andrei Shapiro <andrei.shapiro@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\model\clientConfig;

class GetConfigQuery
{
    private string $extension;
    private string $action;
    private string $module;
    private ?string $shownExtension;
    private ?string $shownStructure;

    public function __construct(
        string $extension,
        string $action,
        string $module,
        ?string $shownExtension,
        ?string $shownStructure
    ) {
        $this->extension = $extension;
        $this->action = $action;
        $this->module = $module;
        $this->shownExtension = $shownExtension;
        $this->shownStructure = $shownStructure;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getModule(): string
    {
        return $this->module;
    }

    public function getShownExtension(): ?string
    {
        return $this->shownExtension;
    }

    public function getShownStructure(): ?string
    {
        return $this->shownStructure;
    }
}
