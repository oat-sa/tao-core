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
 * This container initialize the import form.
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage actions_form
 */
class tao_actions_form_Import
extends tao_helpers_form_FormContainer
{
	// --- ASSOCIATIONS ---


	// --- ATTRIBUTES ---

	private $importHandlers = array();

	/**
	 * @var tao_helpers_form_Form
	 */
	private $subForm = null;
	// --- OPERATIONS ---

	/**
	 * Initialise the form for the given importHandlers
	 * 
	 * @param array $importHandlers
	 * @param tao_helpers_form_Form $subForm
	 */
	public function __construct($importHandler, $availableHandlers, $class) {
		$this->importHandlers = $availableHandlers;
		$this->subForm = $importHandler->getForm();
		parent::__construct(array(
			'importHandler' => get_class($importHandler),
			'classUri'		=> $class->getUri()
		));
	}

	/**
	 * inits the import form
	 *
	 * @access public
	 * @author Joel Bout, <joel.bout@tudor.lu>
	 * @return mixed
	 */
	public function initForm()
	{
		$this->form = tao_helpers_form_FormFactory::getForm('import');
		 
		$submitElt = tao_helpers_form_FormFactory::getElement('import', 'Free');
		$submitElt->setValue( "<a href='#' class='form-submiter' ><img src='".TAOBASE_WWW."/img/import.png' /> ".__('Import')."</a>");

		$this->form->setActions(array($submitElt), 'bottom');
		$this->form->setActions(array(), 'top');
		 
	}

	/**
	 * Inits the element to select the importhandler
	 * and takes over the elements of the import form
	 *
	 * @access public
	 * @author Joel Bout, <joel.bout@tudor.lu>
	 * @return mixed
	 */
	public function initElements()
	{
		//create the element to select the import format
		$formatElt = tao_helpers_form_FormFactory::getElement('importHandler', 'Radiobox');
		$formatElt->setDescription(__('Choose import format'));
		$formatElt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty')); // should never happen anyway
		$importHandlerOptions= array();
		foreach ($this->importHandlers as $importHandler) {
			$importHandlerOptions[get_class($importHandler)] = $importHandler->getLabel();
		}
		$formatElt->setOptions($importHandlerOptions);
		

		$classUriElt = tao_helpers_form_FormFactory::getElement('classUri', 'Hidden');
//		$classUriElt->setValue($class->getUri());
		$this->form->addElement($classUriElt);

		$this->form->addElement($formatElt);
		$this->form->createGroup('formats', __('Supported formats to import'), array('importHandler'));

		if (!is_null($this->subForm)) {
			//load dynamically the method regarding the selected format
			foreach ($this->subForm->getElements() as $element) {
				$this->form->addElement($element);
			}
			foreach ($this->subForm->getGroups() as $group) {
				$this->form->createGroup($group['title'],$group['title'],$group['elements'],$group['options']);
			}
		}
	}

}

?>