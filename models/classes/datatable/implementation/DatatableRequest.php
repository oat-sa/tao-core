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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */
namespace oat\tao\model\datatable\implementation;

use oat\tao\model\datatable\DatatableRequest as DatatableRequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use GuzzleHttp\Psr7\ServerRequest;

/**
 * Class DatatableRequest
 * @package oat\tao\model\datatable
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class DatatableRequest implements DatatableRequestInterface
{

    const DEFAULT_ROWS = 25;
    const DEFAULT_PAGE = 1;
    const DEFAULT_SORT_BY = null;
    const DEFAULT_SORT_ORDER = 'asc';
    const DEFAULT_SORT_TYPE = 'string';
    const DEFAULT_FILTERS = [];

    /**
     * @var array
     */
    private $requestParams;

    /**
     * DatatableRequest constructor.
     * @param ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request)
    {
        $bodyParams = $request->getParsedBody();
        if ($bodyParams === null) {
            $bodyParams = [];
        }
        $queryParams = $request->getQueryParams();
        $this->requestParams = array_merge($bodyParams, $queryParams);
    }

    /**
     * Get amount of records per page
     * @return integer
     */
    public function getRows()
    {
        $rows = isset($this->requestParams[self::PARAM_ROWS])
            ? $this->requestParams[self::PARAM_ROWS]
            : self::DEFAULT_ROWS;

        return (integer)$rows;
    }

    /**
     * Get page number
     * @return integer
     */
    public function getPage()
    {
        $page = isset($this->requestParams[self::PARAM_PAGE]) ? $this->requestParams[self::PARAM_PAGE] : self::DEFAULT_PAGE;
        return (integer)$page;
    }

    /**
     * Get sorting column name
     * @return string
     */
    public function getSortBy()
    {
        $sortBy = isset($this->requestParams[self::PARAM_SORT_BY]) ?
            $this->requestParams[self::PARAM_SORT_BY] : self::DEFAULT_SORT_BY;
        return $sortBy;
    }

    /**
     * Get sorting direction
     * @return string
     */
    public function getSortOrder()
    {
        $sortOrder = isset($this->requestParams[self::PARAM_SORT_ORDER]) ?
            $this->requestParams[self::PARAM_SORT_ORDER] : self::DEFAULT_SORT_ORDER;
        $sortOrder = mb_strtolower($sortOrder);

        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = self::DEFAULT_SORT_ORDER;
        }

        return $sortOrder;
    }

    /**
     * Get sorting type
     * @return string
     */
    public function getSortType()
    {
        $sortType = isset($this->requestParams[self::PARAM_SORT_TYPE]) ?
            $this->requestParams[self::PARAM_SORT_TYPE] : self::DEFAULT_SORT_TYPE;
        $sortType= mb_strtolower($sortType);

        if(!in_array($sortType, ['string', 'numeric'])) {
            $sortType = self::DEFAULT_SORT_TYPE;
        }

        return $sortType;
    }

    /**
     * Get filters
     * @return array
     */
    public function getFilters()
    {
        $filters = isset($this->requestParams[self::PARAM_FILTERS]) ?
            $this->requestParams[self::PARAM_FILTERS] : self::DEFAULT_FILTERS;

        return $filters;
    }

    /**
     * Get DatatableRequest populated from superglobal arrays
     * @return DatatableRequestInterface
     */
    public static function fromGlobals()
    {
        return new static(ServerRequest::fromGlobals());
    }
}
