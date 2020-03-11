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
 * Copyright (c) 2014-2020 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\scripts\install;

use oat\oatbox\extension\script\ScriptAction;

class SetUpOAuthLockoutService extends ScriptAction
{
    public const OPT_VERBOSE = 'verbose';
    public const OPT_NOLOCK = 'implementation';
    public const OPT_STORAGE = 'storage';

    public const STORAGE = ['kv', 'rds'];

    public function run()
    {
    }

    protected function setKvStorage()
    {
    }

    protected function setRdsStorage()
    {
    }

    protected function setNoLockout()
    {
        // sets NpoLockout implementation, that does nothing
    }

    protected function provideOptions()
    {
        return [
            self::OPT_NOLOCK => [
                'prefix'      => 'n',
                'flag'        => true,
                'longPrefix'  => self::OPT_NOLOCK,
                'default'     => true,
                'description' => 'If true, set NoLockout implementation, otherwise it will require defining storage as well.',
            ],
            self::OPT_STORAGE => [
                'prefix'      => 's',
                'longPrefix'  => self::OPT_STORAGE,
                'required'    => false,
                'description' => 'Storage for lockout service, may be Rds or Kv',
            ],
            self::OPT_VERBOSE     => [
                'prefix'      => 'v',
                'longPrefix'  => self::OPT_VERBOSE,
                'flag'        => true,
                'description' => 'Output the log as command output.',
            ],
        ];
    }

    protected function provideDescription()
    {
        return 'Script sets up and configures selected lockout implementation.';
    }
}
