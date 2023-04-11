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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA.
 *
 * @author Andrei Shapiro <andrei.shapiro@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\model\clientConfig\handlers;

use tao_helpers_Date;
use oat\tao\helpers\dateFormatter\DateFormatterInterface;
use oat\tao\model\clientConfig\ClientLibConfigHandlerInterface;

class DateTimeClientLibConfigHandler implements ClientLibConfigHandlerInterface
{
    public function __invoke(array $config): array
    {
        $config['util/locale']['dateTimeFormat'] = $this->getDateFormatter()->getJavascriptFormat(
            tao_helpers_Date::FORMAT_LONG
        );

        return $config;
    }

    private function getDateFormatter(): DateFormatterInterface
    {
        return tao_helpers_Date::getDateFormatter();
    }
}
