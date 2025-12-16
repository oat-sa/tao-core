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

namespace oat\tao\test\unit\webhooks\task;

use JsonSchema\Validator;
use PHPUnit\Framework\TestCase;
use oat\tao\model\webhooks\task\JsonWebhookPayloadFactory;

class JsonWebhookPayloadFactoryTest extends TestCase
{
    public function testGetContentType()
    {
        $factory = new JsonWebhookPayloadFactory();
        $this->assertSame('application/json', $factory->getContentType());
    }

    public function testCreatePayloadSchema()
    {
        $factory = new JsonWebhookPayloadFactory();
        /** @noinspection PhpUnhandledExceptionInspection */
        $payload = $factory->createPayload('TestEvent', '52a3de8dd0f270fd193f9f4bff05232f', 1237435, ['d' => 4]);
        $payloadObject = json_decode($payload, false);

        $validator = new Validator();
        $validator->validate($payloadObject, (object)[
            '$ref' => 'file://' . realpath(__DIR__ . '/webhookRequestSchema.json')
        ]);
        if (!$validator->isValid()) {
            $validationErrors = [];
            foreach ($validator->getErrors() as $error) {
                $validationErrors[] = sprintf('[%s] %s', $error['property'], $error['message']);
            }
            $this->fail(implode(PHP_EOL, $validationErrors));
        }
        $this->assertTrue(true);
    }

    public function testCreatePayloadData()
    {
        $factory = new JsonWebhookPayloadFactory();
        /** @noinspection PhpUnhandledExceptionInspection */
        $payload = $factory->createPayload('TestEvent', '52a3de8dd0f270fd193f9f4bff05232f', 1237435, ['d' => 4]);
        $payloadObject = json_decode($payload, true);

        $sourceUrl = defined('ROOT_URL') ? ROOT_URL : gethostname();
        self::assertSame($sourceUrl, $payloadObject[JsonWebhookPayloadFactory::SOURCE]);

        $events = $payloadObject[JsonWebhookPayloadFactory::EVENTS];
        $this->assertCount(1, $events);
        $event = reset($events);

        self::assertSame('TestEvent', $event[JsonWebhookPayloadFactory::EVENT_NAME]);
        self::assertSame('52a3de8dd0f270fd193f9f4bff05232f', $event[JsonWebhookPayloadFactory::EVENT_ID]);
        self::assertSame(1237435, $event[JsonWebhookPayloadFactory::TRIGGERED_TIMESTAMP]);
        self::assertSame(['d' => 4], $event[JsonWebhookPayloadFactory::EVENT_DATA]);
    }
}
