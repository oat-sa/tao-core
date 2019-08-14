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

class WebhookTaskParamsFactory extends ConfigurableService
{
    /**
     * @param array $array
     * @throws \InvalidArgumentException
     * @return WebhookTaskParams
     */
    public function createFromArray(array $array)
    {
        $this->validateKeys($array);
        $this->validateTypes($array);

        return new WebhookTaskParams(
            $array[WebhookTaskParams::EVENT_NAME],
            $array[WebhookTaskParams::EVENT_ID],
            $array[WebhookTaskParams::TRIGGERED_TIMESTAMP],
            $array[WebhookTaskParams::EVENT_DATA],
            $array[WebhookTaskParams::WEBHOOK_CONFIG_ID]
        );
    }

    private function validateKeys(array $array)
    {
        $requiredParams = [
            WebhookTaskParams::EVENT_NAME,
            WebhookTaskParams::WEBHOOK_CONFIG_ID,
            WebhookTaskParams::EVENT_DATA,
            WebhookTaskParams::EVENT_ID,
            WebhookTaskParams::TRIGGERED_TIMESTAMP
        ];
        $missedParams = array_diff($requiredParams, array_keys($array));

        if (count($missedParams) > 0) {
            throw new \InvalidArgumentException('Params '. implode(', ', $missedParams) . ' not found');
        }
    }

    private function validateTypes(array $array)
    {
        $errors = [];

        if (!is_string($array[WebhookTaskParams::EVENT_NAME])) {
            $errors[] = WebhookTaskParams::EVENT_NAME . ' is not a string';
        }

        if (!is_string($array[WebhookTaskParams::WEBHOOK_CONFIG_ID])) {
            $errors[] = WebhookTaskParams::WEBHOOK_CONFIG_ID . ' is not a string';
        }

        if (!is_array($array[WebhookTaskParams::EVENT_DATA])) {
            $errors[] = WebhookTaskParams::EVENT_DATA . ' is not an array';
        }

        if (!is_string($array[WebhookTaskParams::EVENT_ID])) {
            $errors[] = WebhookTaskParams::EVENT_ID . ' is not a string';
        }

        if (!is_int($array[WebhookTaskParams::TRIGGERED_TIMESTAMP])) {
            $errors[] = WebhookTaskParams::TRIGGERED_TIMESTAMP . ' is not an integer';
        }

        if (count($errors) > 0) {
            throw new \InvalidArgumentException('Invalid params types: ' . implode(', ', $errors));
        }
    }
}
