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
 * Validate string lenght
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 */
class tao_helpers_form_validators_Length
    extends tao_helpers_form_Validator
{
    public function setOptions(array $options)
    {
        parent::setOptions($options);
        
        if($this->hasOption('min') && $this->hasOption('max')){
            $this->setMessage(__('Invalid field length')." (minimum ".$this->getOption('min').", maximum ".$this->getOption('max').")");
        }
        else if($this->hasOption('min') && !$this->hasOption('max')){
            $this->setMessage(__('This field is too short')." (minimum ".$this->getOption('min').")");
        }
        else if(!$this->hasOption('min') && $this->hasOption('max')){
            $this->setMessage(__('This field is too long')." (maximum ".$this->getOption('max').")");
        }
        else{
            throw new Exception("Please set 'min' and/or 'max' options!");
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
        $returnValue = (bool) false;

        
        $returnValue = true;
        
		$values = is_array($values) ? $values : array($values);
		foreach ($values as $value) {
			if ($this->hasOption('min') && mb_strlen($value) < $this->getOption('min')) {
				if ($this->hasOption('allowEmpty') &&  $this->getOption('allowEmpty') && empty($value)) {
					continue;
				} else {
					$returnValue = false;
					break;
				}
			}
			if ($this->hasOption('max') && mb_strlen($value) > $this->getOption('max')) {
				$returnValue = false;
				break;
			}
		}
        

        return (bool) $returnValue;
    }

}
