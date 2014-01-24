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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

/**
 * This helper aims at providing utility methods to render
 * reports into HTML.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class tao_helpers_report_Rendering {
    
    /**
     * Render a common_report_Report object into an HTML string output.
     * 
     * @param common_report_Report $report A report to be rendered
     * @return string The HTML rendering.
     */
    public static function render(common_report_Report $report) {
        
        $stack = new SplStack();
        $renderingStack = new SplStack();
        $traversed = array();
        
        $stack->push($report);
        
        while ($stack->count() > 0) {
            
            $current = $stack->pop();
            
            if (in_array($current, $traversed, true) === false && $current->hasChildren() === true) {
                // -- Hierarchical report, 1st pass (descending).
                
                // Repush report for a 2ndpass.
                $stack->push($current);
                
                // Tag as already traversed.
                $traversed[] = $current;
                
                // Push the children for a 1st pass.
                foreach ($current as $child) {
                    $stack->push($child);
                }
            }
            else if (in_array($current, $traversed, true) === true && $current->hasChildren() === true) {
                // -- Hierachical report, 2nd pass (ascending).
                
                // Get the nested renderings of the current report.
                $children = array();
                foreach ($current as $child) {
                    $children[] = $renderingStack->pop();
                }
                
                $renderingStack->push(self::renderReport($current, $children));
            }
            else {
                // -- Leaf report, 1st & single pass.
                $renderingStack->push(self::renderReport($current, array()));
            }
        }
        
        return $renderingStack->pop();
    }
    
    /**
     * Contains the logic to render a report and its children.
     * 
     * @param common_report_Report $report A report to be rendered.
     * @param array $childRenderedReports An array of strings containing the separate rendering of $report's child reports.
     * @param integer $nesting The current nesting level (root = 0).
     * @return string The HTML output of $report.
     */
    private static function renderReport(common_report_Report $report, array $childRenderedReports = array()) {
        
        switch ($report->getType()) {
            
            case common_report_Report::TYPE_SUCCESS:
                $typeClass = 'success';
            break;
            
            case common_report_Report::TYPE_WARNING:
                $typeClass = 'warning';
            break;
            
            case common_report_Report::TYPE_ERROR:
                $typeClass = 'error';
            break;
            
            default:
                $typeClass = 'info';
            break;
        }
        
        $openingTag = '<div class="feedback-' . $typeClass . ' tao-scope">';
        $icon = '<span class="icon-' . $typeClass . '"></span>';
        $message = $report->__toString();
        $endingTag = '</div>';
        
        // Put all the children renderings together.
        $content = implode('', $childRenderedReports);
        return $openingTag . $icon . $message . $content . $endingTag;
    }
}