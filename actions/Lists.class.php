<?php
/**
 * This controller provide the actions to manage the lists of data
 * 
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 * @subpackage action
 *
 */
class Lists extends CommonModule {

	/**
	 * @var tao_models_classes_ListService
	 */
	protected $listService = null;
	
	/**
	 * Constructor performs initializations actions
	 * @return void
	 */
	public function __construct(){		
		$this->listService = tao_models_classes_ServiceFactory::get('tao_models_classes_ListService');
		$this->defaultData();
	}

	/**
	 * Show the list of users
	 * @return void
	 */
	public function index(){
		
		$myAdderFormContainer = new tao_actions_form_List();
		$myForm = $myAdderFormContainer->getForm();
		
		if($myForm->isSubmited()){
			if($myForm->isValid()){
				$values = $myForm->getValues();
				$newList = $this->listService->createList($values['label']);
				$i = 0;
				while($i < $values['size']){
					$this->listService->createListElement($newList, __('element'). ' '.($i + 1));
					$i++;
				}
			}
		}
		else{
			$myForm->getElement('label')->setValue(__('List').' '.(count($this->listService->getLists()) + 1));
		}
		$this->setData('form', $myForm->render());
		
		$lists = array();
		foreach($this->listService->getLists() as $listClass){
			$elements = array();
			foreach($this->listService->getListElements($listClass) as $index => $listElement){
				$elements[$index] = array(
					'uri'		=> tao_helpers_Uri::encode($listElement->uriResource),
					'label'		=> $listElement->getLabel()
				);
				ksort($elements);
			}
			$lists[] = array(
				'uri'		=> tao_helpers_Uri::encode($listClass->uriResource),
				'label'		=> $listClass->getLabel(),
				'editable'	=> $listClass->isSubClassOf(new core_kernel_classes_Class(TAO_LIST_CLASS)),
				'elements'	=> $elements
			);
		}
		
		$this->setData('lists', $lists);
		$this->setView('list/index.tpl');
	}

	
	public function saveLists(){
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		$saved = false;
		
		if($this->hasRequestParameter('uri')){
			
			$listClass = $this->listService->getList(tao_helpers_Uri::decode($this->getRequestParameter('uri')));
			if(!is_null($listClass)){
				$listClass->setLabel($this->getRequestParameter('label'));
				
				$setLevel = false;
				$levelProperty = new core_kernel_classes_Property(TAO_LIST_LEVEL_PROP);
				foreach($listClass->getProperties(true) as $property){
					if($property->uriResource == $levelProperty->uriResource){
						$setLevel = true;
						break;
					}
				}
				
				$elements = $this->listService->getListElements($listClass);
				foreach($this->getRequestParameters() as $key => $value){
					if(preg_match("/^list\-element_/", $key)){
						$key = str_replace('list-element_', '', $key);
						$level = substr($key, 0, strpos('_', $key) + 1);
						$uri = tao_helpers_Uri::decode(preg_replace("/^{$level}_/", '', $key));
						
						$found = false;
						foreach($elements as $element){
							if($element->uriResource == $uri && !empty($uri)){
								$found = true;
								$element->setLabel($value);
								if($setLevel){
									$element->editPropertyValues($levelProperty, $level);
								}
								break;
							}
						}
						if(!$found){
							$element = $this->listService->createListElement($listClass, $value);
							if($setLevel){
								$element->setPropertyValue($levelProperty, $level);
							}
						}
					}
				}
				$saved = true;
			}
		}
		echo json_encode(array('saved' => $saved));
	}
	
	public function removeList(){
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		$saved = false;
	}
}
?>