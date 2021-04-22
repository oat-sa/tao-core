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
 * Copyright (c) 2020-2021 (original work) Open Assessment Technologies SA;
 *
 */

declare(strict_types=1);

namespace oat\tao\scripts\tools\index;

use core_kernel_classes_ClassIterator;
use DateTime;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\extension\script\ScriptAction;
use oat\oatbox\reporting\Report;
use oat\search\base\ResultSetInterface;
use oat\tao\model\menu\MenuService;
use oat\tao\model\search\index\IndexIterator;
use oat\tao\model\search\ResultSet;
use oat\tao\model\search\Search;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class IndexPopulator extends ScriptAction implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    use OntologyAwareTrait;

    protected function provideUsage(): array
    {
        return [
            'prefix' => 'h',
            'longPrefix' => 'help',
            'description' => 'Type this option to see the parameters.'
        ];
    }

    protected function provideOptions(): array
    {
        return [
            'limit' => [
                'prefix' => 'l',
                'longPrefix' => 'limit',
                'flag' => false,
                'description' => 'The limit of resources to be processed per class',
                'defaultValue' => 10
            ],
            'indexBatchSize' => [
                'prefix' => 'ibs',
                'longPrefix' => 'indexBatchSize',
                'flag' => false,
                'description' => 'Amount of documents to index on each batch interaction',
                'defaultValue' => 50
            ],
            'offset' => [
                'prefix' => 'o',
                'longPrefix' => 'offset',
                'flag' => false,
                'description' => 'The offset of resources.',
                'defaultValue' => 0
            ],
            'class' => [
                'prefix' => 'c',
                'longPrefix' => 'class',
                'flag' => false,
                'description' => 'The class of resources. If empty, the script will consider the first class to be indexed.',
                'defaultValue' => ""
            ],
            'lock' => [
                'prefix' => 'k',
                'longPrefix' => 'lock',
                'flag' => false,
                'description' => 'The file used to report when there is no more resources to process.',
                'defaultValue' => '.export.lock'
            ],
        ];
    }

    protected function provideDescription(): string
    {
        return 'This script index documents on the search engine';
    }

    /**
     * @inheritDoc
     */
    protected function run(): Report
    {
        $report = Report::createInfo('');
        $classIterator = new core_kernel_classes_ClassIterator($this->getIndexedClasses());
        $currentClass = $this->getOption('class') ?: $classIterator->current()->getUri();
        $limit = (int)$this->getOption('limit');
        $offset = (int)$this->getOption('offset');
        $indexBatchSize = (int)$this->getOption('indexBatchSize');
        $totalProcessed = $offset + $limit;

        $this->logInfo('starting indexation');

        foreach ($classIterator as $class) {
            if (!empty($currentClass) && $currentClass !== $class->getUri()) {
                continue;
            }

            $totalResources = $class->countInstances([], ['recursive' => true]);
            $reportedClass = empty($currentClass) ? $class->getUri() : $currentClass;
            $resources = $this->searchResults($class->getUri(), $offset, $limit);

            $result = $this->processRequestByBatch(
                $report,
                $resources,
                $reportedClass,
                $indexBatchSize,
                $offset,
                $limit
            );

            $isClassFullyProcessed = $totalProcessed >= $totalResources || $result === 0;

            if ($isClassFullyProcessed) {
                $report->add($this->getScriptReport($totalProcessed, $reportedClass, $limit, $offset));

                $classIterator->next();

                if (!$classIterator->valid()) {
                    $message = sprintf(
                        'there is no more resources to be indexed after class %s',
                        $class->getUri()
                    );

                    $report->add(Report::createInfo($message));
                    $this->logInfo($message);

                    $this->finishPagination($reportedClass);

                    break;
                }

                $nextClassUri = $classIterator->current()->getUri();

                $message = sprintf(
                    'next class to be indexed is %s',
                    $nextClassUri
                );

                $this->logInfo($message);

                $this->lockPagination($nextClassUri);
            }

            if (!$isClassFullyProcessed) {
                $this->lockPagination($reportedClass);
            }
        }

        return $report;
    }

    protected function getIndexedClasses(): array
    {
        $classes = [];

        foreach (MenuService::getAllPerspectives() as $perspective) {
            foreach ($perspective->getChildren() as $structure) {
                foreach ($structure->getTrees() as $tree) {
                    $rootNode = $tree->get('rootNode');
                    if (!empty($rootNode)) {
                        $classes[$rootNode] = $this->getClass($rootNode);
                    }
                }
            }
        }

        return array_values($classes);
    }

    protected function getScriptReport(int $result, string $class): Report
    {
        if (0 === $result) {
            return Report::createInfo(
                sprintf(
                    'There is no resources to be indexed for class: %s.',
                    $class
                )
            );
        }

        return Report::createSuccess(
            sprintf(
                'Finished at %s. Indexed %d resources for class: %s.',
                (new DateTime('now'))->format(DateTime::ATOM),
                $result,
                $class
            )
        );
    }

    private function lockPagination(string $classUri): void
    {
        file_put_contents(
            $this->getOption('lock'),
            $classUri
        );
    }

    private function finishPagination(string $classUri): void
    {
        file_put_contents(
            $this->getOption('lock'),
            $classUri . PHP_EOL . 'FINISHED'
        );
    }

    private function searchResults($classUri, int $offset, int $limit): ResultSetInterface
    {
        $search = $this->getComplexSearchService();

        $queryBuilder = $search->query()
            ->setLimit($limit)
            ->setOffset($offset);

        $criteria = $search->searchType($queryBuilder, $classUri, true);

        $queryBuilder = $queryBuilder->setCriteria($criteria);

        return $search->getGateway()->search($queryBuilder);
    }

    private function processRequestByBatch(
        Report $report,
        ResultSetInterface $resources,
        string $classUri,
        int $batchSize,
        int $offset,
        int $limit
    ): int {
        $paginatedResources = $this->groupResourcesByBatch($resources, $batchSize);
        $totalResults = 0;

        foreach ($paginatedResources as $key => $resources) {
            $totalResources = count($resources);

            if ($totalResources === 0) {
                continue;
            }

            $indexIterator = new IndexIterator(new ResultSet($resources, $totalResources));
            $this->propagate($indexIterator);

            $batchResults = $this->getSearch()->index($indexIterator);
            $totalResults += $batchResults;

            if ($batchResults > 0) {
                $message = sprintf(
                    '%s resources indexed for class %s by %s. offset: %s, limit %s',
                    $batchResults,
                    $classUri,
                    static::class,
                    $offset,
                    $limit
                );

                $report->add(Report::createInfo($message));
                $this->logInfo($message);
            }
        }

        return $totalResults;
    }

    private function groupResourcesByBatch(ResultSetInterface $resources, int $batchSize): array
    {
        $batchGroups = [];
        $batchIndex = 0;

        foreach ($resources as $resource) {
            $batchGroups[$batchIndex] = $batchGroups[$batchIndex] ?? [];

            if (count($batchGroups[$batchIndex]) >= $batchSize) {
                $batchIndex++;
            }

            $batchGroups[$batchIndex][] = $resource;
        }

        return $batchGroups;
    }

    private function getComplexSearchService(): ComplexSearchService
    {
        return $this->getServiceLocator()->get(ComplexSearchService::SERVICE_ID);
    }

    private function getSearch(): Search
    {
        return $this->getServiceLocator()->get(Search::SERVICE_ID);
    }
}
