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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\model\webhooks\task;

use JsonSchema\Validator;
use oat\oatbox\service\ConfigurableService;

class JsonValidator extends ConfigurableService
{
    const FILE_PATH = __DIR__ . '/webhookResponseSchema.json';

    /**
     * @param mixed $data
     * @throws InvalidJsonException
     * @throws \common_exception_FileReadFailedException
     */
    public function validate($data)
    {
        $validator = new Validator();
        $validator->validate($data, $this->readSchema());

        if (!$validator->isValid()) {
            $validationErrors = [];
            foreach ($validator->getErrors() as $error) {
                $validationErrors[] = sprintf('[%s] %s', $error['property'], $error['message']);
            }
            throw new InvalidJsonException('JSON check against schema failed', 0, $validationErrors);
        }
    }

    /**
     * @return object
     * @throws \common_exception_FileReadFailedException
     */
    private function readSchema()
    {
        $contents = @file_get_contents(self::FILE_PATH);
        if ($contents === false) {
            throw new \common_exception_FileReadFailedException('JSON schema for response could not be loaded');
        }
        $obj = json_decode($contents, false);
        if ($obj === false) {
            throw new \common_exception_FileReadFailedException(
                'JSON schema for response is not a valid JSON: ' . json_last_error_msg()
            );
        }
        return $obj;
    }
}
