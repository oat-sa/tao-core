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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung
 *                         (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor
 *                         (under the project TAO-SUSTAIN & TAO-DEV);
 *
 */

use oat\generis\model\fileReference\FileReferenceSerializer;
use oat\oatbox\filesystem\File;
use oat\oatbox\service\ServiceManager;
use oat\tao\helpers\form\elements\xhtml\XhtmlRenderingTrait;

/**
 * Short description of class tao_helpers_form_elements_xhtml_File
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 */
class tao_helpers_form_elements_xhtml_File extends tao_helpers_form_elements_File
{
    use XhtmlRenderingTrait;

    /**
     * Short description of method feed
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     */
    public function feed()
    {
        if (isset($_FILES[$this->getName()])) {
            $this->setValue($_FILES[$this->getName()]);
        } else {
            throw new tao_helpers_form_Exception('cannot evaluate the element ' . __CLASS__);
        }
    }

    /**
     * Short description of method render
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return string
     */
    public function render()
    {
        if (! empty($this->value)) {
            if (common_Utils::isUri($this->value)) {
                $referencer = $this->getServiceLocator()->get(FileReferenceSerializer::SERVICE_ID);
                /** @var File $file */
                $file = $referencer->unserialize($this->value);
                if (!$file->exists()) {
                    $referencer->cleanup($this->value);
                }
            }
        }

        $returnValue = $this->renderLabel();
        $returnValue .= "<input type='hidden' name='MAX_FILE_SIZE' value='"
            . tao_helpers_form_elements_File::MAX_FILE_SIZE . "' />";
        $returnValue .= "<input type='file' name='{$this->name}' id='{$this->name}' "
            . "data-testid='{$this->getDescription()}' ";
        $returnValue .= $this->renderAttributes();
        $returnValue .= " value='{$this->value}'  />";

        return (string) $returnValue;
    }

    /**
     * Short description of method getEvaluatedValue
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    public function getEvaluatedValue()
    {
        return $this->getRawValue();
    }

    public function getServiceLocator()
    {
        return ServiceManager::getServiceManager();
    }
}
