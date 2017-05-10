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
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2016 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */

/**
 * A specific Data Binder which binds data coming from a Generis Form Instance
 * the Generis persistent memory.
 *
 * If the target instance was not set, a new instance of the target class will
 * created to receive the data to be bound.
 *
 * @access public
 * @author Jerome Bogaerts <jerome@taotesting.com>
 * @package tao
 
 */

use oat\oatbox\service\ServiceManager;
use oat\generis\model\fileReference\FileReferenceSerializer;
use oat\tao\model\upload\UploadService;

class tao_models_classes_dataBinding_GenerisFormDataBinder extends tao_models_classes_dataBinding_GenerisInstanceDataBinder
{
    /**
     * Simply bind data from a Generis Instance Form to a specific generis class
     *
     * The array of the data to be bound must contain keys that are property
     * The repspective values can be either scalar or vector (array) values or
     * values.
     *
     * - If the element of the $data array is scalar, it is simply bound using
     * - If the element of the $data array is a vector, the property values are
     * with the values of the vector.
     * - If the element is an object, the binder will infer the best method to
     * it in the persistent memory, depending on its nature.
     *
     * @access public
     * @author Jerome Bogaerts <jerome@taotesting.com>
     * @param  array data An array of values where keys are Property URIs and values are either scalar, vector or object values.
     * @return mixed
     */
    public function bind($data)
    {
        $returnValue = null;


        try {
        	$instance = parent::bind($data);

        	// Take care of what the generic data binding did not.
			foreach ($data as $p => $d){
				$property = new core_kernel_classes_Property($p);

				if ($d instanceof tao_helpers_form_data_UploadFileDescription){
					$this->bindUploadFileDescription($property, $d);
				}
			}

        	$returnValue = $instance;
        }
        catch (common_Exception $e){
        	$msg = "An error occured while binding property values to instance '': " . $e->getMessage();
        	throw new tao_models_classes_dataBinding_GenerisFormDataBindingException($msg);
        }


        return $returnValue;
    }

    /**
     * Binds an UploadFileDescription with the target instance.
     *
     * @access protected
     * @author Jerome Bogaerts <jerome@taotesting.com>
     * @param  core_kernel_classes_Property $property The property to bind the data.
     * @param  tao_helpers_form_data_UploadFileDescription $desc the upload file description.
     * @return void
     * @throws \oat\oatbox\service\ServiceNotFoundException
     * @throws \common_Exception
     */
    protected function bindUploadFileDescription(
        core_kernel_classes_Property $property,
        tao_helpers_form_data_UploadFileDescription $desc
    ) {
        $instance = $this->getTargetInstance();

        // If form has delete action, remove file
        if ($desc->getAction() == tao_helpers_form_data_UploadFileDescription::FORM_ACTION_DELETE) {
            $this->removeFile($property);
        }

        // If form has add action, remove file & replace by new
        elseif ($desc->getAction() == tao_helpers_form_data_UploadFileDescription::FORM_ACTION_ADD) {
            $name = $desc->getName();
            $size = $desc->getSize();

            if (! empty($name) && ! empty($size)) {

                // Remove old
                $this->removeFile($property);

                // Move the file at the right place.
                $source = $desc->getTmpPath();
                $serial = tao_models_classes_TaoService::singleton()->storeUploadedFile($source, $name);
                $this->getServiceLocator()->get(UploadService::SERVICE_ID)->remove($source);

                // Create association between item & file, database side
                $instance->editPropertyValues($property, $serial);

                // Update the UploadFileDescription with the stored file.
                $desc->setFile($serial);
            }
        }
    }

    /**
     * Remove file stored into given $property from $targetInstance
     * - Remove file properties
     * - Remove physically file
     * - Remove property link between item & file
     *
     * @param core_kernel_classes_Property $property
     */
    protected function removeFile(core_kernel_classes_Property $property)
    {
        $instance = $this->getTargetInstance();
        $referencer = $this->getServiceLocator()->get(FileReferenceSerializer::SERVICE_ID);

        foreach ($instance->getPropertyValues($property) as $oldFileSerial) {
            /** @var \oat\oatbox\filesystem\File $oldFile */
            $oldFile = $referencer->unserializeFile($oldFileSerial);
            $oldFile->delete();
            $referencer->cleanup($oldFileSerial);
            $instance->removePropertyValue($property, $oldFileSerial);
        }
    }

    /**
     * @return ServiceManager
     */
    public function getServiceLocator()
    {
        return ServiceManager::getServiceManager();
    }

}