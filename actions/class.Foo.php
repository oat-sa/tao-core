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
 */

class tao_actions_Foo extends tao_actions_CommonModule
{

    private function returnSuccess($data)
    {
        return $this->returnJson([
            'success' => true,
            'data'    => $data
        ], 200);
    }
    
    public function getClasses()
    {

        $clazz = new core_kernel_classes_Class($this->getRequestParameter('classUri'));

        $result = [[
            'uri' => $clazz->getUri(),
            'label' => $clazz->getLabel(),
            'children' => $this->getSubClasses($clazz->getSubClasses(false))
        ]];

        $this->returnSuccess($result);
    }

    private function getSubClasses($subClasses)
    {

        $result = [];

        foreach ($subClasses as $subClass) {
            $children = $subClass->getSubClasses(false);
            $entry = [
                uri => $subClass->getUri(),
                label => $subClass->getLabel()
            ];
            if (count($children) > 0) {
                $entry['children'] = $this->getSubClasses($children);
            }
            array_push($result, $entry);
        }

        return $result;
    }

    public function getAllInstances()
    {

        $clazz = new core_kernel_classes_Class($this->getRequestParameter('classUri'));
        $firstName = new core_kernel_classes_Property('http://www.tao.lu/Ontologies/generis.rdf#userFirstName');
        $lastName = new core_kernel_classes_Property('http://www.tao.lu/Ontologies/generis.rdf#userLastName');
        $login = new core_kernel_classes_Property('http://www.tao.lu/Ontologies/generis.rdf#login');
        $mail = new core_kernel_classes_Property('http://www.tao.lu/Ontologies/generis.rdf#userMail');

        $result = [];
        $subClasses = $clazz->getSubClasses(true);
        foreach($subClasses as $subClass){
            $classResults = [];
            foreach($subClass->getInstances() as $instance){
                $classResults[] = [
                    'uri'       => $instance->getUri(),
                    'label'     => $instance->getLabel(),
                    'firstname' => (string)$instance->getOnePropertyValue($firstName),
                    'lastname'  => (string)$instance->getOnePropertyValue($lastName),
                    'login'     => (string)$instance->getOnePropertyValue($login),
                    'mail'      => (string)$instance->getOnePropertyValue($mail)
                ];
            }
            $result[$subClass->getUri()] = $classResults;
        }

        $this->returnJson($result);
    }
}
