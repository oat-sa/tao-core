<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace oat\tao\model\search\strategy;

use oat\tao\model\mvc\view\ViewHelperAbstract;

/**
 * Description of SearchHelpView
 *
 * @author Christophe GARCIA <christopheg@taotesting.com>
 */
class SearchHelpView extends ViewHelperAbstract {
    /**
     * return empty html
     * @return string
     */
    public function render() {
        
        return '
    <span class="icon-help tooltipstered" data-tooltip="~ .tooltip-content:first" data-tooltip-theme="info"></span>
    <div class="tooltip-content">
        <div>
        <strong>enter your keyword here</strong>
        </div>
    </div>';
        
    }
    
}
