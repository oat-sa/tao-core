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

use InvalidArgumentException;

abstract readonly class DataPolicyMessage
{
    private const BACKOFFICE_APP = 'backoffice';

    public string $dataSubjectRawId;
    public string $ownerApp;
    public string $policyId;
    public string $policyVersion;
    public string $tenantId;

    public function __construct(array $data)
    {
        foreach (get_class_vars(static::class) as $property => $value) {
            if (!isset($data[$property])) {
                throw new InvalidArgumentException(sprintf('Required property %s is missing', $property));
            }
        }

        $this->dataSubjectRawId = $data['dataSubjectRawId'];
        $this->ownerApp = $data['ownerApp'];
        $this->policyId = $data['policyId'];
        $this->policyVersion = $data['policyVersion'];
        $this->tenantId = $data['tenantId'];
    }

    public static function fromPayload(string $payload): ?self
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

        return new static($body);
    }

    public function isBackofficeApp(): bool
    {
        return $this->ownerApp === self::BACKOFFICE_APP;
    }

    public function toMessage($additionalData = []): array
    {
        return array_merge(get_object_vars($this), $additionalData);
    }
}
