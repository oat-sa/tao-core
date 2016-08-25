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

abstract class tao_actions_form_AbstractProperty extends tao_helpers_form_FormContainer
{

    /**
     * @var core_kernel_classes_Property
     */
    protected $property;

	/**
	 * @var integer
	 */
	protected $index;

	public function __construct( core_kernel_classes_Class $clazz,  core_kernel_classes_Resource $instance = null, $options = array(), $data = array())
	{
        $this->property = new \core_kernel_classes_Property($instance);
        return parent::__construct($data, $options);
	}

	/**
	 * Property bein authored
	 *
	 * @return core_kernel_classes_Property
	 */
	protected function getPropertyInstance()
	{
	    return $this->property;
	}

	/**
	 * Initialize the form
	 *
	 * @access protected
	 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
	 * @return mixed
	 */
	protected function initForm()
	{


		(isset($this->options['name'])) ? $name = $this->options['name'] : $name = '';
		if(empty($name)){
			$name = 'form_'.(count(self::$forms)+1);
		}
		unset($this->options['name']);

		$this->index = array_key_exists( 'index', $this->options ) ? $this->options['index'] : 1;

		$this->form = tao_helpers_form_FormFactory::getForm($name, $this->options);


	}

    /**
     * Returns if property is inherited or not for class
     * @return bool
     */
    protected function isParentProperty()
    {
        return isset($this->options['isParentProperty']) && $this->options['isParentProperty'];
    }

	/**
	 * Returns html for property
	 * @param core_kernel_classes_Property $property
	 * @return string
	 */
	protected function getGroupTitle($property)
	{
		if ($this->isParentProperty()){
			$domainLabel = array();
			foreach ($property->getDomain()->getIterator() as $domain) {
				$domainLabel[] = $domain->getLabel();
			}

			$groupTitle = '<span class="property-heading-label">' . _dh($property->getLabel()) . '</span>'
				. '<span class="property-heading-toolbar">'
				. _dh(implode(' ', $domainLabel))
				. ' <span class="icon-find"></span>'
				. ' <span class="icon-edit"></span>'
				. '</span>';

		}else{
			$groupTitle = '<span class="property-heading-label">' . _dh($property->getLabel()) . '</span>'
				. '<span class="property-heading-toolbar">'
				. ' <span class="icon-find"></span>'
				. '<span class="icon-edit"></span>'
				. '<span class="icon-bin property-deleter" data-uri=\''.tao_helpers_Display::encodeAttrValue($property->getUri()).'\'></span>'
				. '</span>';
		}
		return $groupTitle;
	}

	/**
	 * @return int
	 */
	protected function getIndex()
	{
		return $this->index;
	}
}