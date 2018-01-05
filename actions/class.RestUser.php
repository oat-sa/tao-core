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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */

use oat\generis\model\GenerisRdf;

/**
 * Class tao_actions_RestUser
 *
 * Rest interface to manage forms to create and edit users.
 *
 * Request should contains following data:
 * [
 *       "http://www.tao.lu/Ontologies/generis.rdf#userFirstName" => "Bertrand",
 *       "http://www.tao.lu/Ontologies/generis.rdf#userLastName"  => "Chevrier",
 *       "http://www.tao.lu/Ontologies/generis.rdf#userMail" => "bertrand@taotesting.com",
 *       "http://www.tao.lu/Ontologies/generis.rdf#userDefLg" => "http://www.tao.lu/Ontologies/TAO.rdf#Langfr-FR",
 *       "http://www.tao.lu/Ontologies/generis.rdf#userUILg" => "http://www.tao.lu/Ontologies/TAO.rdf#Langfr-FR",
 *       "http://www.tao.lu/Ontologies/generis.rdf#login" => "berty",
 *       "http://www.w3.org/2000/01/rdf-schema#label" => "bertounet",
 *       "http://www.tao.lu/Ontologies/generis.rdf#userRoles"=> [
 *          'http://www.tao.lu/Ontologies/TAOProctor.rdf#ProctorRole',
 *          'http://www.tao.lu/Ontologies/TAO.rdf#SysAdminRole'
 *       ],
 *       'password1' => 'ctl789@CTL789@',
 *       'password2' => 'ctl789@CTL789@',
 * ]
 */
class tao_actions_RestUser extends tao_actions_RestResource
{
    /**
     * Return the form object to manage user edition or creation
     *
     * @param $instance
     * @return tao_actions_form_RestUserForm
     */
    protected function getForm($instance)
    {
        $form = new \tao_actions_form_RestUserForm($instance);
        $form->setServiceLocator($this->getServiceManager());
        return $form;
    }

    /**
     * Return the resource parameter
     *
     * @return core_kernel_classes_Resource
     * @InvalidArgumentException If resource does not belong to GenerisRdf::CLASS_GENERIS_USER
     */
    protected function getResourceParameter()
    {
        $resource = parent::getResourceParameter();
        if ($resource->isInstanceOf($this->getClass(GenerisRdf::CLASS_GENERIS_USER))) {
            return $resource;
        }

        throw new InvalidArgumentException('Only user resource are allowed.');
    }

    /**
     * Return the class parameter
     *
     * @return core_kernel_classes_Resource
     * @InvalidArgumentException If class is not an instance GenerisRdf::CLASS_GENERIS_USER
     */
    protected function getClassParameter()
    {
        $class = parent::getClassParameter();
        $rootUserClass = $this->getClass(GenerisRdf::CLASS_GENERIS_USER);

        if ($class->getUri() == $rootUserClass->getUri()) {
            return $class;
        }

        /** @var core_kernel_classes_Class $instance */
        foreach ($rootUserClass->getSubClasses(true) as $instance) {
            if ($instance->getUri() == $class->getUri()) {
                return $class;
            }
        }

        throw new InvalidArgumentException('Only user classes are allowed as classUri.');
    }


}