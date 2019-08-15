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

namespace oat\tao\model\webhooks\configEntity;

use oat\oatbox\service\ConfigurableService;
use oat\tao\helpers\ArrayValidator;

class WebhookEntryFactory extends ConfigurableService
{
    /**
     * @param array $data
     * @return Webhook
     */
    public function createEntryFromArray(array $data)
    {
        $validator = $this->getWebhookEntryValidator();
        if (!$validator->validate($data)) {
            throw new \InvalidArgumentException($validator->getErrorMessage());
        }

        $auth = isset($data[Webhook::AUTH])
            ? $this->createAuthEntryFromArray($data[Webhook::AUTH])
            : null;

        return new Webhook(
            $data[Webhook::ID],
            $data[Webhook::URL],
            $data[Webhook::HTTP_METHOD],
            $auth
        );
    }

    /**
     * @param array $data
     * @return WebhookAuth
     */
    protected function createAuthEntryFromArray(array $data)
    {
        $validator = $this->getWebhookAuthValidator();
        if (!$validator->validate($data)) {
            throw new \InvalidArgumentException(
                'Invalid ' . Webhook::AUTH . ' config: ' . $validator->getErrorMessage()
            );
        }

        return new WebhookAuth(
            $data[WebhookAuth::AUTH_CLASS],
            $data[WebhookAuth::CREDENTIALS]
        );
    }

    /**
     * @return ArrayValidator
     */
    private function getWebhookEntryValidator()
    {
        return (new ArrayValidator())
            ->assertString([Webhook::ID, Webhook::URL, Webhook::HTTP_METHOD])
            ->assertArray(Webhook::AUTH, false, true);
    }

    /**
     * @return ArrayValidator
     */
    private function getWebhookAuthValidator()
    {
        return (new ArrayValidator())
            ->assertString(WebhookAuth::AUTH_CLASS)
            ->assertArray(WebhookAuth::CREDENTIALS);
    }
}
