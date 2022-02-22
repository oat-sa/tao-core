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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 */

namespace oat\tao\model\search\tasks\log;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

class LogBuffer implements LoggerInterface
{
    use LoggerTrait;

    private $buffer = [];

    /** @var LoggerInterface */
    private $next = null;

    public function log($level, $message, array $context = array()): void
    {
        $this->buffer[] = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];

        if (null !== $this->next) {
            $this->next->log($level, $message, $context);
        }
    }

    public function getBuffer(): array
    {
        return $this->buffer;
    }

    public function getFormattedBuffer(): string
    {
        $log = [];
        foreach ($this->buffer as $message)  {
            $log[] = sprintf(
                "%s: %s [%s]",
                $message['level'] ?? '(unknown)',
                $message['message'] ?? '(empty log message)',
                isset($message['context']) ? print_r($message['context'], true) : ''
            );
        }

        return implode("\n", $log);
    }

    public function getNextLogger(): ?LoggerInterface
    {
        return $this->next;
    }

    public function setNextLogger(?LoggerInterface $next): void
    {
        $this->next = $next;
    }
}
