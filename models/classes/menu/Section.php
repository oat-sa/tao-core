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

namespace oat\tao\models\classes\menu;

class Section
{
    private $data = array();
    
    private $trees = array();
    
    private $actions = array();
    
    public function __construct(\SimpleXMLElement $node) {
        $this->data = array(
            'id'  => (string)$node['id'],
            'name'  => (string)$node['name'],
            'url' => (string)$node['url']
        );
        
        foreach ($node->xpath("trees/tree") as $treeNode) {
            $this->trees[] = new Tree($treeNode);
        }
            
        foreach ($node->xpath("actions/action") as $actionNode) {
            $this->actions[] = new Action($actionNode);
        }
    }
    
    public function getId() {
        return $this->data['id'];
    }
    
    public function getUrl() {
        return $this->data['url'];
    }
    
    public function getName() {
        return $this->data['name'];
    }
    
    public function getTrees() {
        return $this->trees;
    }
    
    public function getActions() {
        return $this->actions;
    }
}