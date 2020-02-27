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

use oat\oatbox\service\ConfigurableService;
use oat\tao\helpers\ArrayValidator;

class WebhookTaskParamsFactory extends ConfigurableService
{
    /**
     * @param array $array
     * @throws \InvalidArgumentException
     * @return WebhookTaskParams
     */
    public function createFromArray(array $array)
    {
        $validator = $this->getValidator();
        if (!$validator->validate($array)) {
            throw new \InvalidArgumentException($validator->getErrorMessage());
        }

        return new WebhookTaskParams($array);
    }

    private function getValidator()
    {
        return (new ArrayValidator())
            ->assertString([
                WebhookTaskParams::EVENT_NAME,
                WebhookTaskParams::WEBHOOK_CONFIG_ID,
                WebhookTaskParams::EVENT_ID
            ])
            ->assertArray(WebhookTaskParams::EVENT_DATA)
            ->assertInt([
                WebhookTaskParams::TRIGGERED_TIMESTAMP,
                WebhookTaskParams::RETRY_MAX,
                WebhookTaskParams::RETRY_COUNT
            ])
            ->allowExtraKeys();
    }
}
