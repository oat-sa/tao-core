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

use oat\tao\model\search\index\IndexDocument;
use oat\tao\model\search\Search;
use Psr\Log\LoggerInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use LoggerAwareInterface;

trait SearchTaskLogTrait
{



    private function getErrorMessage(
        string $cause,
        IndexDocument $indexDocument,
        LogBuffer $logBuffer
    ): string
    {
        return sprintf(
            "%s for ID '%s' \n- Indexes: '%s'\n- Document body:\n%s\n- Log: %s",
            $cause,
            $indexDocument->getId(),
            implode(', ', $this->getIndexNames($indexDocument)),
            $this->formatBody($indexDocument),
            $logBuffer->getFormattedBuffer()
        );
    }

    private function formatBody(IndexDocument $indexDocument): string
    {
        $buffer = [];
        foreach ($indexDocument->getBody() as $property => $values) {
            $buffer[] = "{$property} => {$this->formatValue($values)}";
        }

        return '   '.implode("\n   ", $buffer);
    }

    private function formatValue($value): string
    {
        $flags = (JSON_UNESCAPED_SLASHES
                | JSON_UNESCAPED_UNICODE
                | JSON_PARTIAL_OUTPUT_ON_ERROR
                | JSON_INVALID_UTF8_SUBSTITUTE
                | (is_array($value) && count($value) < 2 ? 0x0 : JSON_PRETTY_PRINT)
            );

        return sprintf('(%s) %s', gettype($value), json_encode($value, $flags));
    }

    private function setupLogInterceptor(
        Search $service,
        LoggerInterface $interceptor
    ): ?LoggerInterface {
        if ($service instanceof LoggerAwareInterface && is_callable([$service, 'getLogger'])) {
            $formerLogger = $service->getLogger();
            $service->setLogger($interceptor);

            if ($interceptor instanceof LogBuffer) {
                //@todo We could also add a specific interface for loggers that
                //      allow chained logging
                $interceptor->setNextLogger($formerLogger);
            }

            return $formerLogger;
        }

        return null;
    }

    private function removeLogInterceptor(Search $service, ?LoggerInterface $formerLogger): void
    {
        if ($formerLogger instanceof LoggerInterface) {
            $service->setLogger($formerLogger);
        }
    }

    private function getIndexNames(IndexDocument $indexDocument): array
    {
        return array_keys($indexDocument->getIndexProperties());
    }
}
