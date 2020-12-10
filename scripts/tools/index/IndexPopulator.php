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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 *
 */

declare(strict_types=1);

namespace oat\tao\scripts\tools\index;

use common_report_Report;
use DateTime;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\extension\script\ScriptAction;
use oat\tao\model\menu\MenuService;
use oat\tao\model\search\index\IndexIterator;
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
    protected function run(): common_report_Report
    {
        $report = common_report_Report::createInfo('Excuting');
        $classIterator = new \core_kernel_classes_ClassIterator($this->getIndexedClasses());
        $currentClass = $this->getOption('class');
        $limit = (int)$this->getOption('limit');
        $offset = (int)$this->getOption('offset');
        $result = 0;

        $this->logInfo('starting indexation');
        foreach ($classIterator as $class) {
            if (!empty($currentClass) && $currentClass !== $class->getUri()) {
                continue;
            }
            $reportedClass = empty($currentClass) ? $class->getUri() : $currentClass;

            $search = $this->getServiceLocator()->get(ComplexSearchService::SERVICE_ID);
            $queryBuilder = $search->query()
                ->setLimit($limit)
                ->setOffset($offset);

            $criteria = $search->searchType($queryBuilder, $class->getUri(), true);

            $queryBuilder = $queryBuilder->setCriteria($criteria);
            $resources = $search->getGateway()->search($queryBuilder);

            if ($resources->count() < $limit) {
                $classIterator->next();
                if (!$classIterator->valid()) {
                    $this->logInfo(
                        sprintf('there is no more resources to be indexed for the class %s', $class->getUri())
                    );
                    break;
                }

                $nextClassUri = $classIterator->current()->getUri();
                $this->logInfo(sprintf('next class to be indexed is %s', $nextClassUri));
                file_put_contents($this->getOption('lock'), $nextClassUri);
            }

            $indexIterator = new IndexIterator($resources);
            $indexIterator->setServiceLocator($this->getServiceLocator());
            $searchService = $this->getServiceLocator()->get(Search::SERVICE_ID);
            $result = $searchService->index($indexIterator);

            $this->logInfo(sprintf('%s resources have been indexed by %s', $result, static::class));
            $report->add($this->getScriptReport($result, $reportedClass, $limit, $offset));
        }

        file_put_contents($this->getOption('lock'), $class->getUri() . PHP_EOL . 'FINISHED');

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

    /**
     * @param int $result
     * @return common_report_Report
     * @throws \Exception
     */
    protected function getScriptReport(int $result, string $class, int $limit, int $offset): common_report_Report
    {
        if (0 === $result) {
            return common_report_Report::createInfo(
                sprintf(
                    'There is no resources to be indexed for class: %s, limit: %d, offset: %d',
                    $class,
                    $limit,
                    $offset
                )
            );
        }

        return common_report_Report::createSuccess(
            sprintf(
                'Finished at %s. Indexed %d resources for class: %s, limit: %d, offset: %d.',
                (new DateTime('now'))->format(DateTime::ATOM),
                $result,
                $class,
                $limit,
                $offset
            )
        );
    }
}

