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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 */
namespace oat\tao\model\search\index;

use oat\tao\model\search\SearchTokenGenerator;

class IndexIterator implements \Iterator
{
    const CACHE_SIZE = 100;

    private $classIterator;

    /**
     * Id of the current instance
     *
     * @var int
     */
    private $currentInstance = 0;

    /**
     * List of resource uris currently being iterated over
     *
     * @var array
     */
    private $instanceCache = null;

    /**
     * Indicater whenever the end of  the current cache is also the end of the current class
     *
     * @var boolean
     */
    private $endOfClass = false;

    private $tokenGenerator = null;

    /**
     * Whenever we already moved the pointer, used to prevent unnecessary rewinds
     *
     * @var boolean
     */
    private $unmoved = true;

    /**
     * Constructor of the iterator expecting a class or classes as argument
     *
     * @param mixed $classes array/instance of class(es) to iterate over
     */
    public function __construct($classes) {
        $this->classIterator = new \core_kernel_classes_ClassIterator($classes);
        $this->tokenGenerator = new SearchTokenGenerator();
        $this->ensureNotEmpty();
    }

    /**
     * (non-PHPdoc)
     * @see Iterator::rewind()
     */
    function rewind() {
        if (!$this->unmoved) {
            $this->classIterator->rewind();
            $this->ensureNotEmpty();
            $this->unmoved = true;
        }
    }

    /**
     * (non-PHPdoc)
     * @see Iterator::current()
     */
    function current() {
        $currentResource = new \core_kernel_classes_Resource($this->instanceCache[$this->currentInstance]);
        return $this->createDocument($currentResource);
    }

    /**
     * (non-PHPdoc)
     * @see Iterator::key()
     */
    function key() {
        return $this->classIterator->key().'#'.$this->currentInstance;
    }

    /**
     * (non-PHPdoc)
     * @see Iterator::next()
     */
    function next() {
        $this->unmoved = false;
        if ($this->valid()) {
            $this->currentInstance++;
            if (!isset($this->instanceCache[$this->currentInstance])) {
                // try to load next block (unless we know it's empty)
                $remainingInstances = !$this->endOfClass && $this->load($this->classIterator->current(), $this->currentInstance);

                // endOfClass or failed loading
                if (!$remainingInstances) {
                    $this->classIterator->next();
                    $this->ensureNotEmpty();
                }
            }
        }
    }

    /**
     * While there are remaining classes there are instances to load
     *
     * (non-PHPdoc)
     * @see Iterator::valid()
     */
    function valid() {
        return $this->classIterator->valid();
    }

    // Helpers

    /**
     * Ensure the class iterator is pointin to a non empty class
     * Loads the first resource block to test this
     */
    protected function ensureNotEmpty() {
        $this->currentInstance = 0;
        while ($this->classIterator->valid() && !$this->load($this->classIterator->current(), 0)) {
            $this->classIterator->next();
        }
    }

    /**
     * Load instances into cache
     *
     * @param \core_kernel_classes_Class $class
     * @param int $offset
     * @return boolean
     */
    protected function load(\core_kernel_classes_Class $class, $offset) {
        $results = $class->searchInstances(array(), array(
            'recursive' => false,
            'limit' => self::CACHE_SIZE,
            'offset' => $offset
        ));
        $this->instanceCache = array();
        foreach ($results as $resource) {
            $this->instanceCache[$offset] = $resource->getUri();
            $offset++;
        }

        $this->endOfClass = count($results) < self::CACHE_SIZE;

        return count($results) > 0;
    }

    /**
     * @param \core_kernel_classes_Resource $resource
     * @return IndexDocument
     * @throws \common_exception_InconsistentData
     */
    protected function createDocument(\core_kernel_classes_Resource $resource) {
        $tokenGenerator = new SearchTokenGenerator();

        $body = [];
        foreach ($tokenGenerator->generateTokens($resource) as $data) {
            list($index, $strings) = $data;
            $body[$index->getIdentifier()] = $strings;
        }
        $document = new IndexDocument(
            $resource->getUri(),
            $resource->getUri(),
            $this->classIterator->current()->getUri(),
            $body
        );
        return $document;
    }
}