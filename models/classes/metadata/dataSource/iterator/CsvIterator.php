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
 * Copyright (c) 2016 (update and modification) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\metadata\dataSource\iterator;

/**
 * Class CsvIterator
 * @package oat\tao\model\metadata\dataSource\iterator
 */
class CsvIterator implements \Iterator
{
    const ROW_SIZE = 4096;

    /**
     * The pointer to the cvs file.
     *
     * @var resource
     */
    private $filePointer = null;

    /**
     * The current element, which will be returned on each iteration.
     *
     * @var array
     */
    private $currentElement = null;

    /**
     * The row counter.
     *
     * @var int
     */
    private $rowCounter = null;

    /**
     * The delimiter for the csv file.
     *
     * @var string
     */
    private $delimiter = null;

    /**
     * First iterator line, array of 'numeric key' => 'text value'
     *
     * @var array
     */
    protected $headers;

    /**
     * This is the constructor. It try to open the csv file.The method throws an exception on failure.
     *
     * @param string $file The csv file.
     * @param string $delimiter The delimiter.
     *
     * @throws \Exception
     */
    public function __construct($file, $delimiter=',')
    {
        try {
            $this->filePointer = fopen($file, 'r');
            $this->delimiter = $delimiter;
        }
        catch (\Exception $e) {
            throw new \Exception('The file "'.$file.'" cannot be read.');
        }
    }

    /**
     * Set headers of current header
     *
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * This method resets the file pointer.
     */
    public function rewind()
    {
        $this->rowCounter = 0;
        rewind($this->filePointer);
    }

    /**
     * This method returns the current csv row as a 2 dimensional array
     * Load header value as key of currentElement
     *
     * @return array The current csv row as a 2 dimensional array
     */
    public function current()
    {
        $this->currentElement = fgetcsv($this->filePointer, self::ROW_SIZE, $this->delimiter);
        $data = [];
        if (! $this->headers) {
            $data = $this->currentElement;
        } else {
            foreach ($this->currentElement as $key => $element) {
                $data[$this->headers[$key]] = $element;
            }
        }

        $this->rowCounter++;
        return $data;
    }

    /**
     * This method returns the current row number.
     *
     * @return int The current row number
     */
    public function key()
    {
        return $this->rowCounter;
    }

    /**
     * This method checks if the end of file is reached.
     *
     * @return boolean Returns true on EOF reached, false otherwise.
     */
    public function next()
    {
        return !feof($this->filePointer);
    }

    /**
     * This method checks if the next row is a valid row.
     *
     * @return boolean If the next row is a valid row.
     */
    public function valid()
    {
        if (!$this->next()) {
            fclose($this->filePointer);
            return false;
        }
        return true;
    }
}