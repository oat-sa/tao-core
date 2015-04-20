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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */

/**
 * The XHTML implementation of the Calendar Widget.
 *
 * @author Bertrand Chevrier, <bertrand@taotesting.com>
 * @package tao
 *         
 */
class tao_helpers_form_elements_xhtml_Calendar extends tao_helpers_form_elements_Calendar
{

    /**
     * Rendering of the XHTML implementation of the Calendar Widget.
     *
     * @author Bertrand Chevrier, <bertrand@taotesting.com>
     * @return The XHTML stream of the Calendar Widget.
     */
    public function render()
    {
        $returnValue = (string) '';
        
        $uniqueId = uniqid('calendar_');
        $elementId = tao_helpers_Display::TextCleaner($this->getDescription()) . '_' . $uniqueId;
        $timeZoneName = common_session_SessionManager::getSession()->getTimeZone();
        $now = new DateTime("now", new DateTimeZone('UTC'));
        $timezone = new DateTimeZone($timeZoneName);
        $value = '';
        $utcValue = '';
        
        if (! isset($this->attributes['noLabel'])) {
            $returnValue .= "<label class='form_desc calendar' for='{$this->name}'>" . _dh($this->getDescription()) . "</label>";
        } else {
            unset($this->attributes['noLabel']);
        }
        
        if (! isset($this->attributes['size'])) {
            $this->attributes['size'] = 20;
        }
        
        if (! empty($this->value)) {
            $timeStamp = is_numeric($this->getRawValue()) ? $this->getRawValue() : $this->getEvaluatedValue();
            $value = _dh(tao_helpers_Date::displayeDate($timeStamp, tao_helpers_Date::FORMAT_DATEPICKER));
            $utcValue = _dh(tao_helpers_Date::displayeDate($timeStamp, tao_helpers_Date::FORMAT_DATEPICKER, 'UTC'));
        }
        
        $returnValue .= "<input type='text' id='$elementId' ";
        $returnValue .= $this->renderAttributes();
        $returnValue .= ' value="' . $value . '" />';
        $returnValue .= "<input type='hidden' name='{$this->name}' id='{$elementId}_alt' value='{$utcValue}' data-timezone='" . ($timezone->getOffset($now) / 60) . "' /> ";
        $returnValue .= "<script type=\"text/javascript\">
                    require(['ui/calendar'], function (Calendar) {
                        new Calendar({
                            pickerOptions : {
                                controlType: 'select',
                                timezoneList : " . json_encode($this->getTimeZones()) . "
                            },
                            selector : '#{$elementId}',
                            altFieldSelector :'#{$elementId}_alt',
                        });
                    });</script>";
        
        return (string) $returnValue;
    }
    
    /**
     * Gets the Unix timestamp.
     * Note: this method always return time in UTC timezone.
     * @return string
     */
    public function getEvaluatedValue()
    {
        $returnValue = $this->getRawValue();
        
        if (!empty($returnValue)) {
            $tz = new DateTimeZone('UTC');
            $dt = new DateTime($returnValue, $tz);
            $returnValue = (string) $dt->getTimestamp();
        }
        
        return $returnValue;
    }

    /**
     * Function generates array of time zones
     * 
     * @return array Example:
     *         <pre>
     *         array(
     *           array('label' => '-12:00', 'value' => -720),
     *           ...
     *           array('label' => '+14:00', 'value' => 840)
     *         )
     *         </pre>
     */
    private function getTimeZones()
    {
        $results = array();
        $now = new DateTime("now", new DateTimeZone('UTC'));
        $timeZones = array_unique(\DateTimeZone::listIdentifiers());
        foreach ($timeZones as $key) {
            $timezone = new DateTimeZone($key);
            if (isset($results[$timezone->getOffset($now)])) continue;
            
            $sign = ($timezone->getOffset($now) == 0) ? '' : ($timezone->getOffset($now) > 0 ? '+' : '-');
            
            $results[$timezone->getOffset($now)] = array(
                'label' => $sign . gmdate("H:i", abs($timezone->getOffset($now))),
                'value' => $timezone->getOffset($now) / 60
            );
        }
        ksort($results);
        return array_values($results);
    }
}
