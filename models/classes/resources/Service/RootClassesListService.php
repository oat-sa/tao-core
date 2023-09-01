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

namespace oat\tao\model\resources\Service;

use oat\tao\model\menu\Tree;
use core_kernel_classes_Class;
use oat\tao\model\menu\Section;
use oat\tao\model\menu\MenuService;
use oat\tao\model\menu\Perspective;
use oat\generis\model\data\Ontology;
use oat\tao\model\resources\Contract\RootClassesListServiceInterface;

class RootClassesListService implements RootClassesListServiceInterface
{
    /** @var Ontology */
    private $ontology;

    /** @var Perspective[] */
    private $perspectives;

    /** @var array<string, string[]|core_kernel_classes_Class[]> */
    private $cache = [];

    /**
     * @param Perspective[]|null $perspectives
     */
    public function __construct(Ontology $ontology, array $perspectives = null)
    {
        $this->ontology = $ontology;
        $this->perspectives = $perspectives ?? MenuService::getAllPerspectives();
    }

    /**
     * {@inheritdoc}
     */
    public function list(): array
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $rootClasses = [];

            foreach ($this->listUris() as $rootClassUri) {
                $rootClasses[] = $this->ontology->getClass($rootClassUri);
            }

            $this->cache[__FUNCTION__] = $rootClasses;
        }

        return $this->cache[__FUNCTION__];
    }

    /**
     * {@inheritdoc}
     */
    public function listUris(): array
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $rootClassesUris = [];

            foreach ($this->perspectives as $perspective) {
                /** @var Section $structure */
                foreach ($perspective->getChildren() as $structure) {
                    /** @var Tree $tree */
                    foreach ($structure->getTrees() as $tree) {
                        $rootClassUri = $tree->get('rootNode');

                        if (!empty($rootClassUri)) {
                            $rootClassesUris[] = $rootClassUri;
                        }
                    }
                }
            }

            $this->cache[__FUNCTION__] = $rootClassesUris;
        }

        return $this->cache[__FUNCTION__];
    }
}
