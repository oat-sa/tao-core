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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\datatable;


/**
 * Class AbstractPayload
 *
 * It will return data formatted to feed the client datatable tao/views/js/ui/datatable.js
 *
 * @package oat\oatbox\task
 * @author Antoine Robin, <antoine@taotesting.com>
 */
abstract class AbstractPayload implements \JsonSerializable
{


    /** @var DatatableRequest $request */
    protected $request;


    /**
     * Count the number of object satisfying the filters
     * @return integer
     */
    abstract protected function count();

    /**
     * Return the data to return to the client
     *
     * @return array
     */
    abstract protected function getData();

    /**
     * Return the payload to display to user
     * @return array formatted to feed the client
     */
    private function getPayload() {

        $countTotal = $this->count();
        $rows = $this->request->getRows();
        $data = $this->getData();
        $data = [
            'rows'    => $rows,
            'page'    => $this->request->getPage(),
            'amount' => count($data),
            'total'   => ceil($countTotal/$rows),
            'data' => $data,
        ];

        return $data;
    }

    public function jsonSerialize()
    {
        return $this->getPayload();
    }


}
