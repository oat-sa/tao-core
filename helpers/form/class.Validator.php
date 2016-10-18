<?php

use oat\oatbox\validator\ValidatorInterface;
use oat\oatbox\Configurable;
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
 *               2016 (update and modification) Open Assessment Technologies SA;
 * 
 */

/**
 * The validators enable you to perform a validation callback on a form element.
 * It's provide a model of validation and must be overridden.
 *
 * @abstract
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 
 */
abstract class tao_helpers_form_Validator extends Configurable
    implements ValidatorInterface
{
    /**
     * Message to the user
     *
     * @access protected
     * @var string
     */
    protected $message = null;

    // --- OPERATIONS ---

    /**
     * Short description of method getName
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return string
     */
    public function getName()
    {
        return (string) str_replace('tao_helpers_form_validators_', '', get_class($this));
    }

    /**
     * Short description of method getMessage
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return string
     */
    public function getMessage()
    {
        return is_null($this->message) ? $this->getDefaultMessage() : $this->message;
    }

    /**
     * Short description of method getMessage
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return string
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }
    
    /**
     * @return string
     */
    protected function getDefaultMessage()
    {
        return __('');
    }

    /**
     * Short description of method evaluate
     *
     * @abstract
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  values
     * @return boolean
     */
    public abstract function evaluate($values);

}