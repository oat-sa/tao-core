<?php

namespace oat\tao\test\unit\webhooks\task;

use PHPUnit\Framework\TestCase;
use oat\tao\model\webhooks\task\WebhookTaskParams;
use oat\tao\model\webhooks\task\WebhookTaskParamsFactory;

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

class WebhookTaskParamsFactoryTest extends TestCase
{
    public function testCreateFromArrayPositive()
    {
        $factory = new WebhookTaskParamsFactory();
        $params = [
            WebhookTaskParams::EVENT_ID => 'idd',
            WebhookTaskParams::TRIGGERED_TIMESTAMP => 1233245,
            WebhookTaskParams::WEBHOOK_CONFIG_ID => 'wh1',
            WebhookTaskParams::EVENT_NAME => 'EventName',
            WebhookTaskParams::EVENT_DATA => ['d' => 4],
            WebhookTaskParams::RETRY_MAX => 5,
            WebhookTaskParams::RETRY_COUNT => 1
        ];
        $params = $factory->createFromArray($params);
        $this->assertInstanceOf(WebhookTaskParams::class, $params);
        $this->assertSame('idd', $params->getEventId());
        $this->assertSame(1233245, $params->getTriggeredTimestamp());
        $this->assertSame('EventName', $params->getEventName());
        $this->assertSame('wh1', $params->getWebhookConfigId());
        $this->assertSame(['d' => 4], $params->getEventData());
        $this->assertSame(5, $params->getRetryMax());
    }

    public function testCreateFromArrayMissedKeys()
    {
        $factory = new WebhookTaskParamsFactory();
        $params = [
            WebhookTaskParams::EVENT_ID => 'idd',
            WebhookTaskParams::TRIGGERED_TIMESTAMP => 1233245,
            WebhookTaskParams::WEBHOOK_CONFIG_ID => 'wh1',
            WebhookTaskParams::EVENT_NAME => 'EventName',
            WebhookTaskParams::EVENT_DATA => ['d' => 4]
        ];

        foreach ($params as $key => $value) {
            $modifiedParams = $params;
            unset($modifiedParams[$key]);
            try {
                $factory->createFromArray($modifiedParams);
            } catch (\InvalidArgumentException $exception) {
                $this->assertStringContainsString($key, $exception->getMessage());
                continue;
            }
            $this->fail("No exception for missed '$key' key in params");
        }
    }

    public function testCreateFromArrayInvalidTypes()
    {
        $factory = new WebhookTaskParamsFactory();
        $params = [
            WebhookTaskParams::EVENT_ID => 42245,
            WebhookTaskParams::TRIGGERED_TIMESTAMP => '1233245',
            WebhookTaskParams::WEBHOOK_CONFIG_ID => 'wh1',
            WebhookTaskParams::EVENT_NAME => 'EventName',
            WebhookTaskParams::EVENT_DATA => ['d' => 4]
        ];

        $this->expectException(\InvalidArgumentException::class);
        try {
            $factory->createFromArray($params);
        } catch (\InvalidArgumentException $exception) {
            $this->assertStringContainsString(WebhookTaskParams::EVENT_ID, $exception->getMessage());
            $this->assertStringContainsString(WebhookTaskParams::TRIGGERED_TIMESTAMP, $exception->getMessage());
            throw $exception;
        }
    }
}
