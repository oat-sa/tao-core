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

    public function getClasses()
    {

        $options = array(
                'subclasses' => true,
                'instances' => false,
                'highlightUri' => '',
                'chunk' => false,
                'offset' => 0,
                'limit' => 0
        );

        $clazz = new core_kernel_classes_Class($this->getRequestParameter('classUri'));

        $result = $this->getSubClasses($clazz->getSubClasses(false));

        $this->returnJson($result);
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

    public function getInstances()
    {

        $clazz = new core_kernel_classes_Class($this->getRequestParameter('classUri'));
        $result = [];
        foreach($clazz->getInstances() as $instance){
            $result[] = [
                'uri' => $instance->getUri(),
                'label' => $instance->getLabel()
            ];
        }

        $this->returnJson($result);
    }
}
