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

namespace oat\tao\model\DataPolicyOrchestrator\Model;

readonly class DataRemovalMessage implements DataPolicyMessageInterface
{
    public string $dataSubjectRawId;
    public string $ownerApp;
    public string $policyId;
    public string $policyVersion;
    public string $tenantId;
    public string $uniqueId;
    public string $name;
    public string $storageType;
    public array $metadata;

    public function __construct(array $data)
    {
        $this->dataSubjectRawId = $data['dataSubjectRawId'];
        $this->ownerApp = $data['ownerApp'];
        $this->policyId = $data['policyId'];
        $this->policyVersion = $data['policyVersion'];
        $this->tenantId = $data['tenantId'];
        $this->uniqueId = $data['uniqueId'];
        $this->name = $data['name'];
        $this->storageType = $data['storageType'];
        $this->metadata = $data['metadata'];
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
