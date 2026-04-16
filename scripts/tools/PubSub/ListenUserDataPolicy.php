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

namespace oat\tao\scripts\tools\PubSub;

use oat\oatbox\extension\script\ScriptAction;
use oat\oatbox\reporting\Report;
use oat\tao\model\Observer\GCP\UserDataRemoval\PubSubUserDataPolicyListener;

class ListenUserDataPolicy extends ScriptAction
{
    private const OPTION_MAX_MESSAGES = 'max-messages';
    private const OPTION_WAIT_SECONDS = 'wait-seconds';
    private const OPTION_MAX_ITERATIONS = 'max-iterations';

    protected function provideOptions(): array
    {
        return [
            self::OPTION_MAX_MESSAGES => [
                'prefix' => 'm',
                'longPrefix' => self::OPTION_MAX_MESSAGES,
                'description' => 'Max Pub/Sub messages per pull',
                'required' => false,
                'cast' => 'int',
                'defaultValue' => 10,
            ],
            self::OPTION_WAIT_SECONDS => [
                'prefix' => 'w',
                'longPrefix' => self::OPTION_WAIT_SECONDS,
                'description' => 'Delay between polling iterations (seconds)',
                'required' => false,
                'cast' => 'int',
                'defaultValue' => 5,
            ],
            self::OPTION_MAX_ITERATIONS => [
                'prefix' => 'i',
                'longPrefix' => self::OPTION_MAX_ITERATIONS,
                'description' => 'How many polling iterations to run, 0 means infinite',
                'required' => false,
                'cast' => 'int',
                'defaultValue' => 0,
            ],
        ];
    }

    protected function provideDescription(): string
    {
        return 'Listen to Pub/Sub data policy topics.';
    }

    protected function run(): Report
    {
        $listener = $this->getServiceManager()->getContainer()->get(PubSubUserDataPolicyListener::class);
        $listener->run(
            $this->getOption(self::OPTION_MAX_MESSAGES),
            $this->getOption(self::OPTION_WAIT_SECONDS),
            $this->getOption(self::OPTION_MAX_ITERATIONS)
        );

        return Report::createSuccess('Pub/Sub data policy listener has finished.');
    }
}
