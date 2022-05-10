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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\Observer\GCP;

use ErrorException;
use Google\Cloud\PubSub\PubSubClient;

class PubSubClientFactory
{
    /** @var array */
    private $environmentVars;

    public function __construct(array $environmentVars = null)
    {
        $this->environmentVars = $environmentVars ?? $_ENV;
    }

    /**
     * @return \Google\Cloud\PubSub\PubSubClient
     *
     * @throws ErrorException
     */
    public function create(array $config = [])
    {
        if (!class_exists(PubSubClient::class) || !$this->getPubSubFilePath()) {
            throw new ErrorException('PubSubClient requirements missing');
        }

        return new PubSubClient(
            [
                'keyFilePath' => $this->getPubSubFilePath(),
            ]
        );
    }

    private function getPubSubFilePath(): ?string
    {
        return $this->environmentVars['GOOGLE_APPLICATION_CREDENTIALS'] ?? null;
    }
}
