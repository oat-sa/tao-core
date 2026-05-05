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

use oat\tao\model\DataPolicyOrchestrator\Config\ConfirmationStatus;

class DataRemovalConfirmationMessage implements DataPolicyMessageInterface
{
    public readonly string $dataSubjectRawId;
    public readonly string $ownerApp;
    public readonly string $policyId;
    public readonly string $policyVersion;
    public readonly string $tenantId;
    public readonly string $uniqueId;
    public readonly string $name;
    public readonly string $storageType;
    public readonly array $errors;
    public readonly string $status;

    public function __construct(array $data, array $errors)
    {
        $this->dataSubjectRawId = $data['dataSubjectRawId'];
        $this->ownerApp = $data['ownerApp'];
        $this->policyId = $data['policyId'];
        $this->policyVersion = $data['policyVersion'];
        $this->tenantId = $data['tenantId'];
        $this->uniqueId = $data['uniqueId'];
        $this->name = $data['name'];
        $this->storageType = $data['storageType'];
        $this->errors = $errors;
        $this->status = ConfirmationStatus::byErrors($errors);
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
