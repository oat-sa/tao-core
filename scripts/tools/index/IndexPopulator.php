<?php
/**
 * **********************************************************************
 * Department  of Assessment, Forecasting and Performance (Depp) Confidential
 * Direction de l'evaluation, de la prospective et de la performance (DEPP) Confidential
 * _________________
 *
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of DEPP and its suppliers, if any. The
 * intellectual and technical concepts contained herein are
 * proprietary to DEPP and its suppliers, and are protected
 * by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from DEPP.
 * ***********************************************************************
 */

declare(strict_types=1);

namespace oat\tao\scripts\tools\index;

use common_report_Report;
use DateTime;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\extension\script\ScriptAction;
use oat\tao\elasticsearch\SimpleResourceIterator;
use oat\tao\model\menu\MenuService;
use oat\tao\model\resources\ResourceIterator;
use oat\tao\model\search\index\IndexIterator;
use oat\tao\model\search\Search;
use oat\taoDepp\scripts\tools\audioRecordingExporter\domain\exception\EmptyUsersException;
use oat\taoDepp\scripts\tools\audioRecordingExporter\domain\service\AudioResponseExporterService;
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
                'prefix' => 'l',
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

            $search = $this->getServiceLocator()->get(ComplexSearchService::SERVICE_ID);
            $queryBuilder = $search->query()
                ->setLimit($limit)
                ->setOffset($offset);

            $criteria = $search->searchType($queryBuilder, $class->getUri(), false);

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

            return $this->getScriptReport($result);
        }

        file_put_contents($this->getOption('lock'), $class->getUri() . PHP_EOL . 'FINISHED');

        return $this->getScriptReport($result);
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
    protected function getScriptReport(int $result): common_report_Report
    {
        return common_report_Report::createSuccess(
            sprintf(
                'Finished at %s. Number of resources indexed is %d.',
                (new DateTime('now'))->format(DateTime::ATOM),
                $result
            )
        );
    }
}