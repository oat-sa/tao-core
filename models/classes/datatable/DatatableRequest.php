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
namespace oat\tao\model\datatable;

/**
 * Interface DatatableRequest
 * @package oat\tao\model\datatable
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
interface DatatableRequest
{
    const PARAM_ROWS = 'rows';
    const PARAM_PAGE = 'page';
    const PARAM_SORT_BY = 'sortby';
    const PARAM_SORT_ORDER = 'sortorder';
    const PARAM_SORT_TYPE = 'sorttype';
    const PARAM_FILTERS = 'filtercolumns';

    /**
     * Get amount of records per page
     * @return integer
     */
    public function getRows();

    /**
     * Get page number
     * @return integer
     */
    public function getPage();

    /**
     * Get sorting column name
     * @return string
     */
    public function getSortBy();

    /**
     * Get sorting direction
     * @return string
     */
    public function getSortOrder();

    /**
     * Get sorting type
     * @return string
     */
    public function getSortType();

    /**
     * Get filters
     * @return array
     */
    public function getFilters();
}
