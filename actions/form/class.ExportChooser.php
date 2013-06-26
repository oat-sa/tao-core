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
 * This container initialize the export form.
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage actions_form
 */
class tao_actions_form_ExportChooser
    extends tao_helpers_form_FormContainer
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---
    private $exportHandlers = array();

    // --- OPERATIONS ---

    public function __construct($exportHandlers, $data)
    {
    	$this->exportHandlers = $exportHandlers;
    	parent::__construct($data);
    }
	/**
     * Short description of method initForm
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    public function initForm()
    {

    	$this->form = new tao_helpers_form_xhtml_Form('exportChooser');

		$this->form->setDecorators(array(
			'element'			=> new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div')),
			'group'				=> new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div', 'cssClass' => 'form-group')),
			'error'				=> new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div', 'cssClass' => 'form-error ui-state-error ui-corner-all')),
			'actions-bottom'	=> new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div', 'cssClass' => 'form-toolbar')),
			'actions-top'		=> new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div', 'cssClass' => 'form-toolbar'))
		));


		$this->form->setActions(array(), 'bottom');
    }

    /**
     * Short description of method initElements
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    public function initElements()
    {

    	//create the element to select the import format
    	$formatElt = tao_helpers_form_FormFactory::getElement('exportHandler', 'Radiobox');
    	$formatElt->setDescription(__('Please select the way to export the data'));

    	//mandatory field
    	$formatElt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
    	$formatElt->setOptions($this->getFormats());
    	
		if (isset($_POST['exportHandler'])) {
			if (array_key_exists($_POST['exportHandler'], $this->getFormats())) {
				$formatElt->setValue($_POST['exportHandler']);
			}
		}

    	$this->form->addElement($formatElt);
    	$this->form->createGroup('formats', __('Supported export formats'), array('exportHandler'));

    	if(isset($this->data['instance'])){
    		$item = $this->data['instance'];
    		if($item instanceof core_kernel_classes_Resource){
				//add an hidden elt for the instance Uri
				$uriElt = tao_helpers_form_FormFactory::getElement('uri', 'Hidden');
				$uriElt->setValue($item->getUri());
				$this->form->addElement($uriElt);
    		}
    	}
    	if(isset($this->data['class'])){
    		$class = $this->data['class'];
    		if($class instanceof core_kernel_classes_Class){
    			//add an hidden elt for the class uri
				$classUriElt = tao_helpers_form_FormFactory::getElement('classUri', 'Hidden');
				$classUriElt->setValue($class->getUri());
				$this->form->addElement($classUriElt);
    		}
    	}

    }
    
    private function getFormats() {
    	$returnValue = array();
    	foreach ($this->exportHandlers as $exportHandler) {
    		$returnValue[get_class($exportHandler)] = $exportHandler->getLabel();
    	}
    	return $returnValue;
    } 

}

?>