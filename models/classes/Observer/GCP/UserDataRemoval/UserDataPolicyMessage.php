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

namespace oat\tao\model\Observer\GCP\UserDataRemoval;

use InvalidArgumentException;

class UserDataPolicyMessage
{
    private readonly string $dataSubjectRawId;
    private readonly ?string $ownerApp;
    private readonly ?string $policyId;
    private readonly ?string $policyVersion;
    private readonly ?string $tenantId;
    private readonly ?string $uniqueId;
    private readonly ?string $name;
    private readonly ?string $storageType;

    public function __construct(array $data)
    {
        if (!isset($data['dataSubjectRawId'])) {
            throw new InvalidArgumentException('dataSubjectRawId cannot be null');
        }

        $this->dataSubjectRawId =  $data['dataSubjectRawId'];
        $this->ownerApp = $data['ownerApp'] ?? null;
        $this->policyId = $data['policyId'] ?? null;
        $this->policyVersion = $data['policyVersion'] ?? null;
        $this->tenantId = $data['tenantId'] ?? null;
        $this->uniqueId = $data['uniqueId'] ?? null;
        $this->name = $data['name'] ?? null;
        $this->storageType = $data['storageType'] ?? null;
    }

    public static function fromPubSubPayload(string $payload): ?self
    {
        $decodedPayload = json_decode($payload, true);

        if (!is_array($decodedPayload)) {
            return null;
        }

        $body = $decodedPayload['body'] ?? $decodedPayload;

        if (is_string($body)) {
            $body = json_decode($body, true);
        }

        if (!is_array($body)) {
            return null;
        }

        $dataSubjectRawId = $body['dataSubjectRawId'] ?? null;

        if (!is_string($dataSubjectRawId) || $dataSubjectRawId === '') {
            return null;
        }

        return new self($body);
    }

    public function getOwnerApp(): ?string
    {
        return $this->ownerApp;
    }

    public function getPolicyId(): ?string
    {
        return $this->policyId;
    }

    public function getPolicyVersion(): ?string
    {
        return $this->policyVersion;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function getDataSubjectRawId(): string
    {
        return $this->dataSubjectRawId;
    }

    public function getUniqueId(): ?string
    {
        return $this->uniqueId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getStorageType(): ?string
    {
        return $this->storageType;
    }

    public function toRemovalConfirmationPayload(string $status, array $errors = []): array
    {
        return [
            'dataSubjectRawId' => $this->getDataSubjectRawId(),
            'tenantId' => $this->getTenantId(),
            'uniqueId' => $this->getUniqueId(),
            'ownerApp' => $this->getOwnerApp(),
            'policyId' => $this->getPolicyId(),
            'policyVersion' => $this->getPolicyVersion(),
            'name' => $this->getName(),
            'storageType' => $this->getStorageType(),
            'status' => $status,
            'errors' => $errors,
        ];
    }

    public function toFullRemovalConfirmationPayload(): array
    {
        return [
            'dataSubjectRawId' => $this->getDataSubjectRawId(),
            'tenantId' => $this->getTenantId(),
            'ownerApp' => $this->getOwnerApp(),
            'policyId' => $this->getPolicyId(),
            'policyVersion' => $this->getPolicyVersion(),
        ];
    }
}
