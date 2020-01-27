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
 * Copyright (c) 2018 (update and modification) Open Assessment Technologies SA
 */

namespace oat\tao\model\security;

use oat\oatbox\service\ServiceManager;
use oat\tao\model\metadata\exception\InconsistencyConfigException;

class SignatureValidator
{
    /**
     * @param array $list
     * @param string $signatureFieldName
     * @param string $idFieldName
     *
     * @throws SecurityException
     * @throws InconsistencyConfigException
     */
    public function checkSignatures(array $list, $signatureFieldName = 'signature', $idFieldName = 'id')
    {
        foreach ($list as $item) {
            $this->checkSignature($item[$signatureFieldName], $item[$idFieldName]);
        }
    }

    /**
     * @param string $signature
     * @param mixed $dataToSign data to be signed
     *
     * @throws SecurityException
     * @throws InconsistencyConfigException
     */
    public function checkSignature($signature, ...$dataToSign)
    {
        if (empty($signature)) {
            throw new SecurityException('Empty signature');
        }

        if (!is_string($signature)) {
            throw new SecurityException('Signature should be a string');
        }

        if ($signature !== $this->getSignatureGenerator()->generate(...$dataToSign)) {
            throw new SecurityException('Invalid signature');
        }
    }

    /**
     * @return SignatureGenerator
     */
    private function getSignatureGenerator()
    {
        return ServiceManager::getServiceManager()->get(SignatureGenerator::class);
    }
}
