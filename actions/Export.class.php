<?php
/**
 * This controller provide the actions to export and manage exported data
 * 
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 * @subpackage action
 *
 */
class Export extends CommonModule {

	
	/**
	 * to be overriden if needed
	 * @var tao_actions_form_Import
	 */
	protected $formContainer;
	
	/**
	 * Constructor performs initializations actions
	 * @return void
	 */
	public function __construct(){		
		parent::__construct();
		$this->formContainer = new tao_actions_form_Export();
	}
	
	/**
	 * get the path to save and retrieve the exported files regarding the current extension
	 * @return string the path
	 */
	protected function getExportPath(){
		$exportPath = BASE_PATH . EXPORT_PATH;
		if($this->hasSessionAttribute('currentExtension')){
			$exportPath = ROOT_PATH . '/' .$this->getSessionAttribute('currentExtension') .EXPORT_PATH;
		}
		
		if(!is_dir($exportPath)){
			if(!mkdir($exportPath)){
				throw new Exception("Unable to create {$exportPath}. Check your filesystem!");
			}
		}
		return $exportPath;
	}
	
	/**
	 * The main action.
	 * Display a form to select the export format
	 * @return void
	 */
	public function index(){
		
		$myForm = $this->formContainer->getForm();
		
		//if the form is submited and valid
		if($myForm->isSubmited()){
			if($myForm->isValid()){
				
				//import method for the given format
				if(!is_null($myForm->getValue('format'))){
					
					$exportMethod = 'export'.strtoupper($myForm->getValue('format')).'Data';
					if(method_exists($this, $exportMethod)){
						
						//apply the matching method
						$this->$exportMethod($myForm->getValues());
					}
				}
			}
		}
		$this->setData('myForm', $myForm->render());
		$this->setData('formTitle', __('Export'));
		$this->setView('form/export.tpl', true);
		
	}
	
	/**
	 * action performed when RDF export form is sent
	 * @param array $formValues the posted data
	 */
	protected function exportRDFData($formValues){
		if($this->hasRequestParameter('ontology') && $this->hasRequestParameter('filename')){
			$exportPath = $this->getExportPath();
			
			$refClass = null;
			$localClass = null;
			if($this->hasSessionAttribute('currentExtension')){
				$extension = str_replace('tao', '', $this->getSessionAttribute('currentExtension'));
				$service =  tao_models_classes_ServiceFactory::get($extension);
				if(!is_null($service)){
					$method = 'get'.ucfirst(preg_replace("/s$/", '',$extension)).'Class';
					if(method_exists($service, $method)){
						$refClass = $service->$method();
						if($refClass instanceof core_kernel_classes_Class){
							$localClass = $service->createSubClass($refClass, $extension.' exporter');
						}
					}
				}
			}
			$adapter = new tao_helpers_data_GenerisAdapterRdf();
			switch($formValues['ontology']){
				case 'data':	$rdf =  $adapter->export($localClass); break;
				case 'current':	$rdf =  $adapter->export($refClass); break;
				case 'all':		$rdf =  $adapter->export(); break;
				default: 		$rdf = ''; break;
			}
			if(!empty($rdf)){
				$name = $formValues['filename'].'_'.time().'.rdf';
				$path = tao_helpers_File::concat(array($exportPath, $name));
				if(!tao_helpers_File::securityCheck($path, true)){
					throw new Exception('Unauthorized file name');
				}
				if(file_put_contents($path, $rdf)){
					$this->setData('message', $name.' '.__('exported successfully'));
				}
			}
			
			if($localClass instanceof core_kernel_classes_Class){
				$localClass->delete();
			}
		}
	}
	
	/**
	 * Get the list of files exported for the current module
	 * The output is formated to be received by a JS Grid
	 * @return void
	 */
	public function getExportedFiles(){
		
		$exportPath = $this->getExportPath();
		
		$exportedFiles = array();
		foreach(scandir($exportPath) as $file){
			$path = $exportPath.'/'.$file;
			if(preg_match("/\.(rdf|zip)$/", $file) && !is_dir($path)){
				$exportedFiles[] = array(
					'path'		=> $path,
					'url'		=> str_replace(ROOT_PATH, ROOT_URL, $path),
					'name'		=> substr($file, 0, strrpos($file, '_')),
					'date'		=> date('Y-m-d H:i:s', ((int)substr(preg_replace("/\.(rdf|zip)$/", '', $file), strrpos($file, '_') + 1)))
				);
			}
		}
		
		$page = $this->getRequestParameter('page'); 
		$limit = $this->getRequestParameter('rows'); 
		$sidx = $this->getRequestParameter('sidx');  
		$sord = $this->getRequestParameter('sord'); 
		$start = $limit * $page - $limit; 
		
		if(!$sidx) $sidx =1; 
		
		//slice from start to limit
		$files = array_slice($exportedFiles, $start, $limit);
		
		$col = array();
		foreach($files as $key => $val){
			$col[$key] = $val[$sidx];
		}
		array_multisort($col, ($sord == 'asc') ? SORT_ASC: SORT_DESC, $files);
		
		$count = count($exportedFiles); 
		if( $count >0 ) { 
			$total_pages = ceil($count/$limit); 
		} 
		else { 
			$total_pages = 0; 
		} 
		if ($page > $total_pages){
			$page = $total_pages; 
		}
		
		$response = new stdClass();
		$response->page = $page; 
		$response->total = $total_pages; 
		$response->records = $count; 
		foreach($files as $i => $file) { 
			$response->rows[$i]['id']= $i; 
			$response->rows[$i]['cell']= array(
				$file['name'],
				basename($file['path']),
				$file['date'],
				array($file['url'],
					_url('downloadExportedFiles', null, null, array('filePath' => urlencode($file['path']))),
					_url('deleteExportedFiles', null, null, array('filePath' => urlencode($file['path'])))
				)
			);
		} 
		echo json_encode($response); 
	}
	
	/**
	 * remove the exported files in parameters
	 * @return void
	 */
	public function deleteExportedFiles(){
		$deleted = false;
		if($this->hasRequestParameter('filePath')){
			$path = urldecode($this->getRequestParameter('filePath'));
			if(preg_match("/^".preg_quote($this->getExportPath(), '/')."/", $path)){
				if(tao_helpers_File::securityCheck($path, true)){
					$deleted = tao_helpers_File::remove($path);
				}
			}
		}
		echo json_encode(array('deleted' => $deleted));
	}
	
	/**
	 * download the exported files in parameters
	 * @return void
	 */
	public function downloadExportedFiles(){
		if($this->hasRequestParameter('filePath')){
			
			$path = urldecode($this->getRequestParameter('filePath'));
			if(preg_match("/^".preg_quote($this->getExportPath(), '/')."/", $path) && file_exists($path)){
				$this->setContentHeader(tao_helpers_File::getMimeType(basename($path)));
				header('Content-Disposition: attachment; fileName="'.basename($path).'"');
				echo file_get_contents($path);
				
				return;
			}
		}
		return;
	}
}
?>