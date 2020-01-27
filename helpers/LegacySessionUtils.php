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
 *
 */

namespace oat\tao\helpers;

use PHPSession;

trait LegacySessionUtils
{
    public function hasSessionAttribute($name)
    {
        return PHPSession::singleton()->hasAttribute($name);
    }

    public function getSessionAttribute($name)
    {
        return PHPSession::singleton()->getAttribute($name);
    }

    public function setSessionAttribute($name, $value)
    {
        PHPSession::singleton()->setAttribute($name, $value);
        return $this;
    }

    public function removeSessionAttribute($name)
    {
        PHPSession::singleton()->removeAttribute($name);
        return $this;
    }

    public function clearSession($global = true)
    {
        PHPSession::singleton()->clear($global);
        return $this;
    }
}
