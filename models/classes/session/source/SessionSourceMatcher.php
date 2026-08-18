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

namespace oat\tao\model\session\source;

use common_session_Session;
use InvalidArgumentException;

/**
 * Matches a session against registered source matchers.
 *
 * @license GPL-2.0
 */
class SessionSourceMatcher
{
    public const SOURCE_PORTAL = 'portal';

    /** @var array<string, SessionSourceMatcherInterface[]> */
    private array $sourceMatchers = [];

    public function addSessionSourceMatcher(string $source, SessionSourceMatcherInterface $matcher): void
    {
        $source = strtolower($source);

        if ($this->hasMatcher($source, $matcher)) {
            throw new InvalidArgumentException(
                sprintf('Matcher "%s" is already registered for source "%s".', $matcher::class, $source)
            );
        }

        $this->sourceMatchers[$source][] = $matcher;
    }

    public function matchesSource(string $source, common_session_Session $session): bool
    {
        $source = strtolower($source);

        if (empty($this->sourceMatchers[$source])) {
            return false;
        }

        foreach ($this->sourceMatchers[$source] as $matcher) {
            if ($matcher->matchesSource($session)) {
                return true;
            }
        }

        return false;
    }

    private function hasMatcher(string $source, SessionSourceMatcherInterface $matcher): bool
    {
        foreach ($this->sourceMatchers[$source] ?? [] as $registeredMatcher) {
            if ($registeredMatcher::class === $matcher::class) {
                return true;
            }
        }

        return false;
    }
}
