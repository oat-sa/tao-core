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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\Lists\Business\Service;

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\Lists\Business\Contract\ClassMetadataSearcherInterface;
use oat\tao\model\Lists\Business\Domain\ClassCollection;
use oat\tao\model\Lists\Business\Input\ClassMetadataSearchInput;
use Throwable;

class ClassMetadataSearcherProxy extends ConfigurableService implements ClassMetadataSearcherInterface
{
    use OntologyAwareTrait;

    public const SERVICE_ID = 'tao/ClassMetadataSearcherProxy';
    public const OPTION_ACTIVE_SEARCHER = 'activeSearcher';

    public function findAll(ClassMetadataSearchInput $input): ClassCollection
    {
        $activeSearcherId = $this->getOption(self::OPTION_ACTIVE_SEARCHER, ClassMetadataService::SERVICE_ID);

        try {
            /** @var ClassMetadataSearcherInterface $searcher */
            $searcher = $this->getServiceLocator()->get($activeSearcherId);

            return $searcher->findAll($input);
        } catch (Throwable $exception) {
            $this->logCritical(
                sprintf(
                    'Impossible to perform class metadata search with %s: %s',
                    $activeSearcherId,
                    $exception->getMessage()
                )
            );

            if ($activeSearcherId !== ClassMetadataService::SERVICE_ID) {
                return $this->getClassMetadataSearcher()->findAll($input);
            }
        }

        return new ClassCollection(...[]);
    }

    private function getClassMetadataSearcher(): ClassMetadataSearcherInterface
    {
        return $this->getServiceLocator()->get(ClassMetadataService::SERVICE_ID);
    }
}
