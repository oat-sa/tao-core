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

/**
 * Describes rendering information for tao_helpers_form_FormElement instances.
 * 
 * @author Jerome Bogaerts, <jerome@taotesting.com>
 *
 */
class tao_helpers_form_FormElementRenderingInfo
{
	/**
	 * Describes that the element is the first to be displayed within
	 * a collection of elements.
	 * 
	 * @var int
	 */
	const FIRST = 0;
	
	/**
	 * Describes that the element is displayed somewhere in the middle
	 * of a collection of elements.
	 * 
	 * @var int
	 */
	const MIDDLE = 1;
	
	/**
	 * Describes that the element is the last to be displayed withing
	 * a collection of elements.
	 * 
	 * @var int
	 */
	const LAST = 2;
	
	/**
	 * The position of the element within a collection of other elements.
	 * The value of this field can be self::FIRST, self::MIDDLE or self::LAST.
	 * 
	 * @var int
	 */
	private $position;
	
	/**
	 * Creates a new instance of tao_helpers_form_FormElementRenderingInfo.
	 * 
	 * @param int position The position of the element to be displayed within a collection of elements (FIRST|MIDDLE|LAST).
	 */
	public function __construct($position)
	{
		$this->setPosition($position);
	}
	
	/**
	 * Get the position of the element within a collection of other elements.
	 * 
	 * @return int The position of the element (FIRST|MIDDLE|LAST).
	 */
	public function getPosition()
	{
		return $this->position;
	}
	
	/**
	 * Set the position of the element within a collection of other elements.
	 * 
	 * @param int $position The position of the element (FIST|MIDDLE|LAST).
	 */
	public function setPosition($position)
	{
		$this->position = $position;
	}
}
?>