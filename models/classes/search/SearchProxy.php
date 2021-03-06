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
 */

declare(strict_types=1);

namespace oat\tao\model\search;

use Exception;
use InvalidArgumentException;
use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;
use oat\tao\model\TaoOntology;
use Psr\Http\Message\ServerRequestInterface;

class SearchProxy extends ConfigurableService implements Search
{
    use OntologyAwareTrait;

    public const OPTION_ADVANCED_SEARCH_CLASS = 'advanced_search_class';
    public const OPTION_DEFAULT_SEARCH_CLASS = 'default_search_class';

    private const GENERIS_SEARCH_WHITELIST = [
        GenerisRdf::CLASS_ROLE,
        TaoOntology::CLASS_URI_TAO_USER,
    ];

    private const DISABLE_URI_SEARCH_FOR_ROOT_CLASSES = [
        'results',
    ];

    public function getAdvancedSearch(): ?SearchInterface
    {
        return $this->getService(self::OPTION_ADVANCED_SEARCH_CLASS);
    }

    public function getDefaultSearch(): SearchInterface
    {
        $defaultSearch = $this->getService(self::OPTION_DEFAULT_SEARCH_CLASS);

        if ($defaultSearch) {
            return $defaultSearch;
        }

        throw new InvalidArgumentException(sprintf('Option %s is required', self::OPTION_DEFAULT_SEARCH_CLASS));
    }

    public function withAdvancedSearch(SearchInterface $search): self
    {
        $this->setOption(self::OPTION_ADVANCED_SEARCH_CLASS, $search);

        return $this;
    }

    public function withDefaultSearch(SearchInterface $search): self
    {
        $this->setOption(self::OPTION_DEFAULT_SEARCH_CLASS, $search);

        return $this;
    }

    /**
     * @throws Exception
     */
    public function searchByQuery(SearchQuery $query): array
    {
        $results = $this->executeSearch($query);

        return $this->getResultSetResponseNormalizer()
            ->normalize($query, $results, '');
    }

    /**
     * @throws Exception
     */
    public function search(ServerRequestInterface $request): array
    {
        $query = $this->getQueryFactory()->create($request);
        $queryParams = $request->getQueryParams();
        $results = $this->executeSearch($query);

        return $this->getResultSetResponseNormalizer()
            ->normalize($query, $results, $queryParams['params']['structure']);
    }

    /**
     * @inheritDoc
     */
    public function query($queryString, $type, $start = 0, $count = 10, $order = 'id', $dir = 'DESC')
    {
        return $this->getIndexSearch()->query($queryString, $type, $start, $count, $order, $dir);
    }

    /**
     * @inheritDoc
     */
    public function flush()
    {
        return $this->getIndexSearch()->flush();
    }

    /**
     * @inheritDoc
     */
    public function index($documents)
    {
        return $this->getIndexSearch()->index($documents);
    }

    /**
     * @inheritDoc
     */
    public function remove($resourceId)
    {
        return $this->getIndexSearch()->remove($resourceId);
    }

    /**
     * @inheritDoc
     */
    public function supportCustomIndex()
    {
        return $this->getIndexSearch()->supportCustomIndex();
    }

    private function executeSearch(SearchQuery $query): ResultSet
    {
        if ($query->isEmptySearch()) {
            return new ResultSet([], 0);
        }

        if ($this->allowIdentifierSearch($query)) {
            $result = $this->getIdentifierSearcher()->search($query);

            if ($result->getTotalCount() > 0) {
                return $result;
            }
        }

        if ($this->isForcingDefaultSearch($query) || !$this->getAdvancedSearchChecker()->isEnabled()) {
            return $this->getDefaultSearch()->query(
                $query->getTerm(),
                $query->getParentClass(),
                $query->getStartRow(),
                $query->getRows()
            );
        }

        return $this->getAdvancedSearch()->query(
            $this->getAdvancedSearchQueryString($query),
            $query->getRootClass(),
            $query->getStartRow(),
            $query->getRows()
        );
    }

    private function getResultSetResponseNormalizer(): ResultSetResponseNormalizer
    {
        return $this->getServiceLocator()->get(ResultSetResponseNormalizer::class);
    }

    private function getAdvancedSearchChecker(): AdvancedSearchChecker
    {
        return $this->getServiceLocator()->get(AdvancedSearchChecker::class);
    }

    private function getIdentifierSearcher(): IdentifierSearcher
    {
        return $this->getServiceLocator()->get(IdentifierSearcher::class);
    }

    private function getQueryFactory(): SearchQueryFactory
    {
        return $this->getServiceLocator()->get(SearchQueryFactory::class);
    }

    private function isForcingDefaultSearch(SearchQuery $query): bool
    {
        return in_array($query->getParentClass(), self::GENERIS_SEARCH_WHITELIST, true);
    }

    private function allowIdentifierSearch(SearchQuery $query): bool
    {
        return !in_array($query->getRootClass(), self::DISABLE_URI_SEARCH_FOR_ROOT_CLASSES, true);
    }

    private function getIndexSearch(): SearchInterface
    {
        return $this->getAdvancedSearchChecker()->isEnabled()
            ? $this->getAdvancedSearch()
            : $this->getDefaultSearch();
    }

    private function getService(string $option): ?SearchInterface
    {
        if (!$this->hasOption($option)) {
            return null;
        }

        /** @var SearchInterface $search */
        $search = $this->getOption($option);

        $this->propagate($search);

        return $search;
    }

    private function getAdvancedSearchQueryString(SearchQuery $query): string
    {
        return sprintf(
            '%s AND parent_classes: "%s"',
            $query->getTerm(),
            $query->getParentClass()
        );
    }
}
