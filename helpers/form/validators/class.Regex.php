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
 *               2022 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

use oat\tao\helpers\form\validators\PreliminaryValidationInterface;

/**
 * @author Joel Bout, <joel.bout@tudor.lu>
 */
class tao_helpers_form_validators_Regex extends tao_helpers_form_Validator implements PreliminaryValidationInterface
{
    public const USER_FORM_SERVICE_ID = self::class . '::USER_FORM';

    public function isPreValidationRequired(): bool
    {
        return $this->getOption('isPreValidationRequired', false);
    }

    public function setOptions(array $options)
    {
        parent::setOptions($options);

        if (!$this->hasOption('format')) {
            throw new common_Exception("Please set the format options (define your regular expression)!");
        }

        if ($this->hasOption('message')) {
            $this->setMessage($this->getOption('message'));
        }
    }

    /**
     * Short description of method evaluate
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  values
     * @return boolean
     */
    public function evaluate($values)
    {
        $returnValue = false;

        if (is_string($values) || is_numeric($values)) {
            $returnValue = (preg_match($this->getOption('format'), $values) === 1);
        }

        return $returnValue;
    }

    protected function getDefaultMessage()
    {
        return __('The format of this field is not valid.');
    }
}
