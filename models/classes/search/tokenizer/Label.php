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

use \core_kernel_classes_Resource;

/**
 * RDFS labels Tokenizer.
 * 
 * A Tokenizer dedicated to tokenize RDFS labels.
 * 
 * @author Joel Bout <joel@taotesting.com>
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 */
class Label implements Tokenizer
{	
    /**
     * Get tokenized strings from core_kernel_classes_Resource values.
     * 
     * @param \core_kernel_classes_Resource[]|\core_kernel_classes_Resource $values An array of core_kernel_classes_Resource objects.
     * @see \oat\tao\model\search\tokenizer\Tokenizer::getStrings()
     */
    public function getStrings($values)
    {
        $strings = array();
        
        if (is_array($values) === false) {
            $values = array($values);
        }
        
        foreach ($values as $valueUri) {
            if (!empty($valueUri)) {
                $value = new core_kernel_classes_Resource($valueUri);
                $label = $value->getLabel();
                $strings[] = (empty($label) === false) ? $label : '';
            }
        }
        
        return $strings;
    }
}