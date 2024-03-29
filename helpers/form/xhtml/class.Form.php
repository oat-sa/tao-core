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
 * (under the project TAO-TRANSFER);
 *              2009-2012 (update and modification) Public Research Centre Henri Tudor
 * (under the project TAO-SUSTAIN & TAO-DEV);
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 */

use oat\oatbox\log\LoggerAwareTrait;

/**
 * Short description of class tao_helpers_form_xhtml_Form
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao

 */
// phpcs:ignore
class tao_helpers_form_xhtml_Form extends tao_helpers_form_Form
{
    use LoggerAwareTrait;

    /**
     * Short description of method getValues
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string $groupName
     * @param  array $filterProperties List of properties which values are unneeded and must be filtered
     * @return array
     */
    public function getValues($groupName = '')
    {
        $returnValue = [];

        foreach ($this->elements as $element) {
            if (!empty($this->systemElements) && in_array($element->getName(), $this->systemElements)) {
                continue;
            }
            if (
                empty($groupName)
                || in_array($element->getName(), $this->groups[$groupName]['elements'])
            ) {
                $returnValue[tao_helpers_Uri::decode($element->getName())] = $element->getEvaluatedValue();
            }
        }


        return (array) $returnValue;
    }

    /**
     * Evaluate the form
     */
    public function evaluate()
    {
        $this->initElements();

        if (!isset($_POST[$this->name . '_sent'])) {
            $this->preValidate();

            return;
        }

        $this->submited = true;

        // Set posted values
        foreach ($this->elements as $id => $element) {
            $this->elements[$id]->feed();
        }

        $this->validate();
    }

    /**
     * Short description of method render
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return string
     */
    public function render()
    {
        $returnValue = '';

        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $action = strpos($requestUri, '?') > 0
            ? substr($requestUri, 0, strpos($requestUri, '?'))
            : $requestUri;

        // Defensive code, prevent double leading slashes issue.
        if (strpos($action, '//') === 0) {
            $action = substr($action, 1);
        }

        $returnValue .= "<div class='xhtml_form'>\n";

        $returnValue .= "<form method='post' id='{$this->name}' name='{$this->name}' action='$action' ";
        if ($this->hasFileUpload()) {
            $returnValue .= "enctype='multipart/form-data' ";
        }

        $returnValue .= ">\n";

        $returnValue .= "<input type='hidden' class='global' name='{$this->name}_sent' value='1' />\n";

        if (!empty($this->error)) {
            $returnValue .= '<div class="xhtml_form_error">' . $this->error . '</div>';
        }

        $returnValue .= $this->renderElements();

        $returnValue .= $this->renderActions();

        $returnValue .= "</form>\n";
        $returnValue .= "</div>\n";



        return $returnValue;
    }

    /**
     * Validate the form
     *
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return bool
     */
    protected function validate()
    {
        $returnValue = true;
        $this->valid = true;

        foreach ($this->elements as $element) {
            if (!$element->validate()) {
                $this->valid = false;
            }
        }

        return $returnValue;
    }

    private function preValidate(): void
    {
        $this->valid = true;

        foreach ($this->elements as $element) {
            $element->preValidate();

            if (!$element->isValid()) {
                $this->valid = false;
            }
        }
    }
}
