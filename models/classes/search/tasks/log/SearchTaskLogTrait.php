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

use oat\tao\elasticsearch\ElasticSearch;
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;
use oat\tao\model\search\index\IndexDocument;
use oat\tao\model\search\Search;
use oat\tao\model\search\SearchProxy;
use Psr\Log\LoggerInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Psr\Log\LoggerAwareInterface;

trait SearchTaskLogTrait
{
    use ValueFormatter;

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

    private function setupLogInterceptor(
        Search $service,
        LoggerInterface $interceptor
    ): ?LoggerInterface {
        if ($this->isUsingAdvancedSearch($service)) {
            // $service is an instance of SearchProxy
            $interceptor->debug(
                'Setting up logger for Advanced Search for '.
                get_class($service)
            );

            return $this->doSetupInterceptor($service->getAdvancedSearch(), $interceptor);
        }

        return null;
    }

    private function removeLogInterceptor(Search $service, ?LoggerInterface $formerLogger): void
    {
        // @todo Needs to be rewritten to take into account ES is wrapped into SearchProxy
        if ($formerLogger instanceof LoggerInterface) {
            $service->setLogger($formerLogger);
        }
    }

    private function doSetupInterceptor(
        ElasticSearch $service,
        LoggerInterface $interceptor
    ): ?LoggerInterface {
        $interceptor->debug(
            "Setting up interceptor on an instance of ".
            get_class($service)
        );

        if ($service instanceof LoggerAwareInterface && is_callable([$service, 'getLogger'])) {
            $formerLogger = $service->getLogger();
            $service->setLogger($interceptor);

            $interceptor->debug(
                sprintf('Logger %s set on an instance of %s',
                    get_class($interceptor),
                    get_class($service)
                )
            );

            if ($interceptor instanceof LogBuffer) {
                //@todo We could also add a specific interface for loggers that
                //      allow chained logging
                $interceptor->setNextLogger($formerLogger);
            } else {
                $interceptor->debug(
                    sprintf('Not setting interceptor for %s: it is not an instanceof %s',
                        get_class($interceptor),
                        LogBuffer::class
                    )
                );
            }

            return $formerLogger;
        } else {
            $interceptor->debug(
                sprintf(
                    '%s does not support getLogger or is not an instance of %s',
                    get_class($service),
                    LoggerAwareInterface::class
                )
            );
        }

        return null;
    }

    private function isUsingAdvancedSearch(Search $service): bool
    {
        return $service instanceof SearchProxy
            && $service->getAdvancedSearch() !== null
            && $this->getAdvancedSearchChecker()->isEnabled();
    }

    private function getAdvancedSearchChecker(): AdvancedSearchChecker
    {
        return $this->getServiceLocator()->get(AdvancedSearchChecker::class);
    }

    private function getIndexNames(IndexDocument $indexDocument): array
    {
        return array_keys($indexDocument->getIndexProperties());
    }
}
