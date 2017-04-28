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
 * Short description of class tao_helpers_form_validators_Regex
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 
 */
class tao_helpers_form_validators_Regex extends tao_helpers_form_Validator
{

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
    public function evaluate( $values )
    {
        $returnValue = false;

        if (is_string( $values ) || is_numeric( $values )) {
            $returnValue = (preg_match( $this->getOption('format'), $values ) === 1);
        }

        return $returnValue;
    }

    protected function getDefaultMessage()
    {
        return __('The format of this field is not valid.');
    }

}
