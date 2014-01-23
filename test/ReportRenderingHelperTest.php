<?php
/*  
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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */

require_once dirname(__FILE__) . '/TaoPhpUnitTestRunner.php';
include_once dirname(__FILE__) . '/../includes/raw_start.php';

class ReportRenderingHelperTest extends TaoPhpUnitTestRunner {
	
    public function testRenderSingle() {
       
        $report = common_report_Report::createSuccess('Success!');
        
        $expected = '<div class="report"><p>Success!</p></div>';
        $this->assertEquals($expected, tao_helpers_report_Rendering::render($report));
    }
    
    public function testRenderNested() {
        
        $report = common_report_Report::createSuccess('Success!');
        $report->add(common_report_Report::createSuccess('Another success!'));
        $report->add(common_report_Report::createFailure('Failure!'));
        
        $expected  = '<div class="report">';
        $expected .=   '<p>Success!</p>';
        $expected .=   '<div class="report">';
        $expected .=     '<p>Another success!</p>';
        $expected .=   '</div>';
        $expected .=   '<div class="report">';
        $expected .=     '<p>Failure!</p>';
        $expected .=   '</div>';
        $expected .= '</div>';
        
        $this->assertEquals($expected, tao_helpers_report_Rendering::render($report));
    }
}
?>
