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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

use oat\tao\model\accessControl\Context;

/**
 * FIXME @TODO This is just a PoC
 */
class tao_actions_PocController extends tao_actions_CommonModule
{
    public function index(): void
    {
        $action = 'someActionOrFeature';

        $this->hasWriteAccessByContext(
            new Context(
                [
                    Context::PARAM_CONTROLLER => self::class,
                    Context::PARAM_ACTION => $action,
                ]
            )
        );

        if ('someActionOrFeature') {
            echo 'I have permission to ' . $action;
        } else {
            echo 'I DO NOT have permission to ' . $action;
        }
    }
}
