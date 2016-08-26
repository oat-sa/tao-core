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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\tao\model\search\tokenizer;

use oat\tao\model\search\tokenizer\ItemContentTokenizer\ItemContentTokenizerResolver;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Class FileTokenizer
 * Tokenizer that interprets the values as resources
 * and returns the file content as search tokens
 *
 * @package oat\tao\model\search\tokenizer
 */
class ItemContentTokenizer implements Tokenizer, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * @param \core_kernel_classes_Resource $resource
     * @param \core_kernel_classes_Property[] $values
     * @return array
     */
    public function getStrings(\core_kernel_classes_Resource $resource, $values)
    {
        try {
            $itemFileTokenizer = $this->getItemContentTokenizerResolver()->resolve($resource);
            return $itemFileTokenizer->getStrings($resource, $values);
        } catch (\common_Exception $e) {
            \common_Logger::i($e->getMessage());
            return [];
        }
    }

    /**
     * @return ItemContentTokenizerResolver
     */
    protected function getItemContentTokenizerResolver()
    {
        return $this->getServiceLocator()->get(ItemContentTokenizerResolver::SERVICE_ID);
    }
}