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

namespace oat\tao\scripts\tools\DataPolicyOrchestrator;

use common_ext_ExtensionsManager;
use oat\oatbox\extension\script\ScriptAction;
use oat\oatbox\reporting\Report;
use oat\tao\model\DataPolicyOrchestrator\PubSub\Listener\DataRemovalCheckListener;
use oat\tao\model\DataPolicyOrchestrator\PubSub\Listener\DataRemovalListener;

/**
 * @example php index.php 'oat\tao\scripts\tools\DataPolicyOrchestrator\ListenDataPolicyOrchestrator'
 *          -t removal
 *          [[ --max-messages=10 ]]
 *          [[ --wait-seconds=5 ]]
 *          [[ --max-iterations=0 ]]
 */
class ListenDataPolicyOrchestrator extends ScriptAction
{
    private const REQUIRED_EXTENSIONS = ['tao', 'taoEventLog'];
    private const LISTENERS_BY_TYPE = [
        'removal' => DataRemovalListener::class,
        'removal-check' => DataRemovalCheckListener::class,
    ];
    private const OPTION_TYPE = 'type';
    private const OPTION_MAX_MESSAGES = 'max-messages';
    private const OPTION_WAIT_SECONDS = 'wait-seconds';
    private const OPTION_MAX_ITERATIONS = 'max-iterations';

    protected function provideOptions(): array
    {
        return [
            self::OPTION_TYPE => [
                'prefix' => 't',
                'longPrefix' => self::OPTION_TYPE,
                'description' => 'Pub/Sub subscription type',
                'required' => true,
                'cast' => 'string'
            ],
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
        $type = $this->getOption(self::OPTION_TYPE);

        if (!array_key_exists($type, self::LISTENERS_BY_TYPE)) {
            return Report::createError(
                sprintf(
                    'Provided type "%s" is not allowed. Allowed types are: %s',
                    $type,
                    implode(', ', array_keys(self::LISTENERS_BY_TYPE))
                )
            );
        }

        $report = Report::createInfo('Start listening for data policy orchestrator topics.');
        $report->add($this->logMissingRequiredExtensions());

        $listener = $this->getServiceManager()->getContainer()->get(self::LISTENERS_BY_TYPE[$type]);
        $listener->run(
            $this->getOption(self::OPTION_MAX_MESSAGES),
            $this->getOption(self::OPTION_WAIT_SECONDS),
            $this->getOption(self::OPTION_MAX_ITERATIONS)
        );

        return $report->add(Report::createSuccess('Pub/Sub data policy listener has finished.'));
    }

    private function logMissingRequiredExtensions(): Report
    {
        $report = Report::createInfo('Checking required extensions for data policy orchestrator.');
        $extensionManager = $this->getServiceManager()->getContainer()->get(common_ext_ExtensionsManager::SERVICE_ID);

        foreach (self::REQUIRED_EXTENSIONS as $extensionId) {
            if ($extensionManager->isInstalled($extensionId)) {
                continue;
            }

            $report->add(
                Report::createWarning(
                    sprintf(
                        'Extension "%s" is not installed. User data handled by this extension cannot be removed '
                        . 'automatically and should be removed manually if needed.',
                        $extensionId
                    )
                )
            );
        }

        return $report;
    }
}
