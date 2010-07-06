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
	 * Constructor performs initializations actions
	 * @return void
	 */
	public function __construct(){		
		$this->defaultData();
	}
	
	/**
	 * Export the selected class instance in a flat CSV file
	 * download header sent
	 * @return void
	 */
	public function index(){
		if(!file_exists(EXPORT_PATH)){
			if(!mkdir(EXPORT_PATH)){
				throw new Exception("Unable to create  " .EXPORT_PATH.". Check your filesystem!");
			}
		}
		$formContainer = new tao_actions_form_Export();
		$myForm = $formContainer->getForm();
		
		if($myForm->isSubmited()){
			if($myForm->isValid()){
			
				$refClass = null;
				$localClass = null;
				try{
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
					switch($myForm->getValue('ontology')){
						case 'data':	$rdf =  $adapter->export($localClass); break;
						case 'current':	$rdf =  $adapter->export($refClass); break;
						case 'all':		$rdf =  $adapter->export(); break;
						default: 		$rdf = ''; break;
					}
					if(!empty($rdf)){
						$path = EXPORT_PATH."/".$myForm->getValue('name').'_'.time().'.rdf';
						file_put_contents($path, $rdf);
					}
				}
				catch(Exception $e){
					print $e;
				}
				if($localClass instanceof core_kernel_classes_Class){
					$localClass->delete();
				}
			}
		}
		
		$this->setData('formTitle', __('Export data to RDF'));
		$this->setData('myForm', $myForm->render());
		$this->setView('form/export.tpl', true);
		
	}
	
	/**
	 * Get the list of files exported for the current module
	 * @return void
	 */
	public function getExportedFiles(){
		$exportedFiles = array();
		foreach(scandir(EXPORT_PATH) as $file){
			$path = EXPORT_PATH.'/'.$file;
			if(preg_match("/\.rdf$/", $file) && !is_dir($path)){
				$exportedFiles[] = array(
					'path'		=> $path,
					'url'		=> EXPORT_URL.'/'.$file,
					'name'		=> substr($file, 0, strrpos($file, '_')),
					'date'		=> date('Y-m-d H:i:s', ((int)substr(str_replace('.rdf', '', $file), strrpos($file, '_') + 1)))
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
				"<a href='{$file['url']}' class='nd' target='_blank' ><img src='".TAOBASE_WWW."img/search.png'  title='".__('view')."' />".__('View')."</a>&nbsp;|&nbsp;" .
				"<a href='".tao_helpers_Uri::url('downloadExportedFiles')."?filePath=".urlencode($file['path'])."' class='nd'  ><img src='".TAOBASE_WWW."img/bullet_go.png'  title='".__('download')."' />".__('Download')."</a>&nbsp;|&nbsp;" .
				"<a href='".tao_helpers_Uri::url('deleteExportedFiles')."?filePath=".urlencode($file['path'])."' class='nav nd' ><img src='".TAOBASE_WWW."img/delete.png' title='".__('delete')."' />".__('Delete')."</a>"
			);
		} 
		echo json_encode($response); 
	}
	
	/**
	 * remove the exported files in parameters
	 * @return void
	 */
	public function deleteExportedFiles(){
		if($this->hasRequestParameter('filePath')){
			$path = urldecode($this->getRequestParameter('filePath'));
			if(preg_match("/^".preg_quote(EXPORT_PATH, '/')."/", $path)){
				unlink($path);
			}
		}
		$this->redirect(tao_helpers_Uri::url('index'));
	}
	
	/**
	 * download the exported files in parameters
	 * @return void
	 */
	public function downloadExportedFiles(){
		if($this->hasRequestParameter('filePath')){
			$path = urldecode($this->getRequestParameter('filePath'));
			if(preg_match("/^".preg_quote(EXPORT_PATH, '/')."/", $path) && file_exists($path)){
				
				header('Content-Type: text/xml');
				header('Content-Disposition: attachment; fileName="'.basename($path).'"');
				echo file_get_contents($path);
				return;
			}
		}
		return;
	}
}
?>