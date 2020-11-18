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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 *
 * @author Sergei Mikhailov <sergei.mikhailov@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\scripts\tools;

use common_report_Report as Report;
use Exception;
use InvalidArgumentException;
use oat\oatbox\extension\script\ScriptAction;
use oat\tao\model\security\xsrf\TokenService;

class SetupClientCsrfTokenPoolStore extends ScriptAction
{
    private const OPTION_STORE = 'store';

    protected function provideOptions(): array
    {
        return [
            self::OPTION_STORE => [
                'prefix'      => 's',
                'longPrefix'  => self::OPTION_STORE,
                'required'    => true,
                'description' => "One of {$this->createFormattedStoreOptionValues()}",
            ],
        ];
    }

    protected function provideDescription(): string
    {
        return 'Set a client-side CSRF token pool store preference.';
    }

    protected function run(): Report
    {
        try {
            /** @var TokenService $tokenService */
            $tokenService = $this->getServiceLocator()->get(TokenService::class);

            $storeOption = $this->getStoreOption();

            $tokenService->setOptions(
                array_replace(
                    $tokenService->getOptions(),
                    [
                        TokenService::OPTION_CLIENT_STORE => $storeOption,
                    ]
                )
            );

            $this->getServiceManager()->register(TokenService::SERVICE_ID, $tokenService);
        } catch (Exception $exception) {
            return Report::createFailure($exception->getMessage());
        }

        return Report::createSuccess(
            sprintf(
                'Set "%s"\'s "%s" option to "%s"',
                TokenService::class,
                TokenService::OPTION_CLIENT_STORE,
                $storeOption
            )
        );
    }

    private function getStoreOption(): string
    {
        $value = $this->getOption(self::OPTION_STORE);

        if (!in_array($value, TokenService::CLIENT_STORE_OPTION_VALUES, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    '"%s" must be one of %s, "%s" given.',
                    self::OPTION_STORE,
                    $this->createFormattedStoreOptionValues(),
                    $value
                )
            );
        }

        return $value;
    }

    /**
     * @return string
     */
    private function createFormattedStoreOptionValues(): string
    {
        return sprintf('"%s"', implode('", "', TokenService::CLIENT_STORE_OPTION_VALUES));
    }
}
