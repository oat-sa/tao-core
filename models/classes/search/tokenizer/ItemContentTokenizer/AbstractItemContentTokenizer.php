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

namespace oat\tao\model\search\tokenizer\itemContentTokenizer;

use oat\generis\model\fileReference\FileReferenceSerializer;
use oat\tao\model\search\tokenizer\Tokenizer;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

abstract class AbstractItemContentTokenizer implements Tokenizer, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * Get serializer to persist filesystem object
     *
     * @return FileReferenceSerializer
     */
    protected function getFileReferenceSerializer()
    {
        return $this->getServiceLocator()->get(FileReferenceSerializer::SERVICE_ID);
    }
}