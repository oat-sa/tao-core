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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 *
 * @author Gabriel Felipe Soares <gabriel.felipe.soares@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\scripts\tools\Security;

use oat\oatbox\extension\script\ScriptAction;
use oat\oatbox\reporting\Report;
use oat\tao\model\security\xsrf\TokenService;

/**
 * Clear all tokens created after 120 seconds ago:
 *  php index.php 'oat\tao\scripts\tools\Security\ClearCsrfTokenTool' -t 120
 */
class ClearCsrfTokenTool extends ScriptAction
{
    private const OPTION_TIME_LIMIT = 'time_limit';
    private const OPTION_SLEEP_INTERVAL = 'sleep_internal';

    protected function provideOptions(): array
    {
        return [
            self::OPTION_TIME_LIMIT => [
                'prefix' => 't',
                'longPrefix' => self::OPTION_TIME_LIMIT,
                'description' => 'Clear token created after X amount of seconds',
                'required' => true,
            ],
            self::OPTION_SLEEP_INTERVAL => [
                'prefix' => 's',
                'longPrefix' => self::OPTION_SLEEP_INTERVAL,
                'description' => 'Interval in microseconds to wait to run next batch deletions',
            ],
        ];
    }

    protected function provideDescription(): string
    {
        return 'Delete CSRF tokens';
    }

    protected function provideUsage(): array
    {
        return [
            'prefix' => 'h',
            'longPrefix' => 'help',
            'description' => $this->provideDescription()
        ];
    }

    protected function run(): Report
    {
        $sleepInterval = filter_var($this->getOption(self::OPTION_SLEEP_INTERVAL) ?? 1000, FILTER_VALIDATE_INT);
        $timeLimit = filter_var($this->getOption(self::OPTION_TIME_LIMIT) ?? (24 * 60 * 60), FILTER_VALIDATE_INT);

        try {
            $totalRemoved = $this->getTokenService()->clearAll($sleepInterval, $timeLimit);

            return Report::createSuccess(
                sprintf('A total of %s CSRF tokens were removed', $totalRemoved)
            );
        } catch (Throwable $exception) {
            return Report::createError(
                sprintf(
                    'Error (%s)%s, trace %s',
                    get_class($exception),
                    $exception->getMessage(),
                    $exception->getTraceAsString()
                )
            );
        }
    }

    private function getTokenService(): TokenService
    {
        return $this->getServiceManager()->get(TokenService::SERVICE_ID);
    }
}
