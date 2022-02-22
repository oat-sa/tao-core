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

trait SearchTaskLogTrait
{
    private function getErrorMessage(
        string $cause,
        IndexDocument $indexDocument,
        LogBuffer $logBuffer
    ): string
    {
        return sprintf(
            '%s for documentId \'%s\' (indexes=\'%s\', doc body=\'%s\') log: %s',
            $cause,
            $indexDocument->getId(),
            implode(', ', $this->getIndexNames($indexDocument)),
            $indexDocument->getBody(),
            $logBuffer->getFormattedBuffer()
        );
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
}
