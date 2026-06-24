<?php

/*
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
 * Foundation, Inc., 31 Milk St # 960789 Boston, MA 02196 USA.
 *
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

/**
 * Resolve the action name from CLI arguments before bootstrapping TAO.
 *
 * @param array $argv
 */
function taoTranslateResolveAction(array $argv): ?string
{
    for ($i = 1, $count = count($argv); $i < $count; $i++) {
        $arg = trim($argv[$i]);

        if (preg_match('/^-{1,2}a(?:ction)?=(.+)$/', $arg, $matches)) {
            return strtolower($matches[1]);
        }

        if (in_array($arg, ['-a', '--action'], true) && isset($argv[$i + 1])) {
            return strtolower(trim($argv[$i + 1]));
        }
    }

    return null;
}

/**
 * @param array $argv
 */
function taoTranslateShouldUseStandaloneBootstrap(array $argv): bool
{
    $standaloneActions = ['compile', 'compileall'];
    $action = taoTranslateResolveAction($argv);
    $standaloneMode = getenv('TAO_TRANSLATE_STANDALONE');

    return in_array($action, $standaloneActions, true)
        && $standaloneMode !== false
        && $standaloneMode !== ''
        && $standaloneMode !== '0';
}
