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

use oat\tao\helpers\form\elements\xhtml\XhtmlRenderingTrait;

/**
 * The XHTML implementation of the Calendar Widget.
 *
 * @author Bertrand Chevrier, <bertrand@taotesting.com>
 * @package tao
 */
class tao_helpers_form_elements_xhtml_Calendar extends tao_helpers_form_elements_Calendar
{
    use XhtmlRenderingTrait;

    /**
     * Rendering of the XHTML implementation of the Calendar Widget.
     *
     * @author Bertrand Chevrier, <bertrand@taotesting.com>
     * @return string The XHTML stream of the Calendar Widget.
     */
    public function render()
    {
        $returnValue = $this->renderLabel();

        $uniqueId = uniqid('calendar_');
        $elementId = tao_helpers_Display::TextCleaner($this->getDescription()) . '_' . $uniqueId;

        if ($this->isDisabled()) {
            return $returnValue . sprintf(
                '<input type="text" 
            name="%s"
            id="%s"
            data-testid="%s"
            value="%s"
            %s
            >',
                $this->name,
                $elementId,
                $this->getDescription(),
                $this->getDateOutput(),
                $this->renderAttributes()
            );
        }

        if (! isset($this->attributes['size'])) {
            $this->attributes['size'] = 20;
        }

        $returnValue .= "<div class='form-elt-container'><input class='datepicker-input' type='text' "
            . "name='{$this->name}' id='$elementId' ";
        $returnValue .= $this->renderAttributes();

        if (! empty($this->value)) {
            $returnValue .= ' value="' . $this->getDateOutput() . '"';
        }

        $returnValue .= ' /></div>';

        return $returnValue;
    }

    public function getEvaluatedValue()
    {
        $returnValue = $this->getRawValue();

        if (is_numeric($returnValue)) {
            return $returnValue;
        }

        if (!empty($returnValue)) {
            $tz = new DateTimeZone(common_session_SessionManager::getSession()->getTimeZone());
            try {
                $returnValue = (string) (new DateTime($returnValue, $tz))->getTimestamp();
            } catch (Exception $e) {
                $returnValue = '';
            }
        }

        return $returnValue;
    }

    private function getDateOutput(): string
    {
        $timeStamp = $this->getEvaluatedValue();

        return !empty($timeStamp) ?
            _dh(tao_helpers_Date::displayeDate($timeStamp, tao_helpers_Date::FORMAT_DATEPICKER)) :
            '';
    }
}
