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
 *               2020 (original work) (update and modification) Open Assessment Technologies SA
 */

/**
 * Short description of class tao_helpers_form_validators_Url
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
class tao_helpers_form_validators_Url extends tao_helpers_form_Validator
{

    private const OPTION_ALLOW_EMPTY = 'allow_empty';

    /**
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
    }

    /**
     * @param string $value
     * @return bool
     */
    public function evaluate($value)
    {
        if ('' === $value && $this->hasOption(self::OPTION_ALLOW_EMPTY) && $this->getOption(self::OPTION_ALLOW_EMPTY) == true) {
            return true;
        }

        //backward compatible behavior:
        //scheme should be prepended if not found (pattern includes spelling errors)
        if (preg_match('/^[a-zA-Z]{1,10}[:\/]{1,3}/', $value) === false) {
            $value = 'http://' . $value;
        }

        $returnValue = !(filter_var($value, FILTER_VALIDATE_URL) === false);

        //'isset' is backward compatible behavior
        if (!$this->hasOption('allow_parameters')) {
            $returnValue = $returnValue && (strpos($value, '?') === false);
        }

        return $returnValue;
    }

    /**
     * Default error message
     *
     * @return string
     */
    protected function getDefaultMessage()
    {
        return __('Provided URL is not valid');
    }
}
