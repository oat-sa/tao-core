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

namespace oat\tao\model\search\tokenizer\ItemContentTokenizer;

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\search\tokenizer\Tokenizer;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

class ItemContentTokenizerResolver extends ConfigurableService
{
    use OntologyAwareTrait;

    const SERVICE_ID = 'tao/itemContentTokenizerResolver';

    /**
     * Resolve the file tokenizer class to use from an $item
     *
     * @param \core_kernel_classes_Resource $item
     * @return mixed
     * @throws \common_Exception
     */
    public function resolve(\core_kernel_classes_Resource $item)
    {
        $fileTokenizer = $this->getItemContentTokenizerByModel($this->getItemModel($item));
        if (! is_object($fileTokenizer) || ! $fileTokenizer instanceof Tokenizer) {
            throw new \common_Exception('Tokenizer is not correctly set. FileTokenizer has to inherit tokenizer interface.');
        }

        if ($fileTokenizer instanceof ServiceLocatorAwareInterface) {
            $fileTokenizer->setServiceLocator($this->getServiceLocator());
        }

        return $fileTokenizer;
    }


    /**
     * Return the model of the given $item
     *
     * @param \core_kernel_classes_Resource $item
     * @return \core_kernel_classes_Resource|null
     * @throws \core_kernel_persistence_Exception
     */
    protected function getItemModel(\core_kernel_classes_Resource $item)
    {
        $itemModel = $item->getOnePropertyValue($this->getProperty(TAO_ITEM_MODEL_PROPERTY));
        if ($itemModel instanceof \core_kernel_classes_Resource) {
            return $itemModel;
        }
        return null;
    }

    /**
     * Get item content tokenizer class associated to the given item model
     *
     * @param \core_kernel_classes_Resource $model
     * @return mixed
     * @throws \common_Exception
     */
    protected function getItemContentTokenizerByModel(\core_kernel_classes_Resource $model)
    {
        if (is_null($model)) {
            throw new \common_Exception('No model found.');
        }

        if ($this->hasOption($model->getUri())) {
            return $this->getOption($model->getUri());
        }

        throw new \common_Exception('No fileTokenizer for model ' . $model . '.');
    }
}