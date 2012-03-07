<?php
if(!defined('RDFAPI_INCLUDE_DIR')){
	define('RDFAPI_INCLUDE_DIR', dirname(__FILE__).'/../../../generis/includes/rdfapi-php/api/');
}
require_once(RDFAPI_INCLUDE_DIR . "RdfAPI.php");

/**
 * The ModelCreator enables you to import Ontologies into a TAO module
 *
 * @author Bertrand CHEVRIER <bertrand.chevrier@tudor.lu>
 *
 */
class tao_install_utils_ModelCreator{

	/**
	 * @var string the module namesapce
	 */
	protected $localNs = '';

	/**
	 * Instantiate a creator for a module
	 * @param string $localNamespace
	 */
	public function __construct($localNamespace){
		if(empty($localNamespace) || !preg_match("/^http/", $localNamespace)){
			throw new tao_install_utils_Exception("$localNamespace is not valid namespace URI for the local namespace!");
		}
		$this->localNs = $localNamespace;
		if(!preg_match("/#$/", $this->localNs)){
			$this->localNs .= '#';
		}
	}

	/**
	 * Specifiq method to insert the super user model,
	 * by using a template RDF file
	 * @param array $userData
	 */
	public function insertSuperUser(array $userData){

		if(!isset($userData['login']) || !isset($userData['password'])){
			throw new tao_install_utils_Exception("To create a super user you must provide at least a login and a password");
		}

		$superUserOntology = dirname(__FILE__)."/../ontology/superuser.rdf";

		if(!file_exists($superUserOntology) || !is_readable($superUserOntology)){
			throw new tao_install_utils_Exception("Unable to load ontology : $superUserOntology");
		}

		$doc = new DOMDocument();
		$doc->load ($superUserOntology);

		foreach($userData as $key => $value){
			$tags = $doc->getElementsByTagNameNS('http://www.tao.lu/Ontologies/generis.rdf#', $key);
			foreach($tags as $tag){
				$tag->appendChild($doc->createCDATASection($value));
			}
		}
		return $this->insertLocalModel($doc->saveXML());
	}

	/**
	 * Insert a model into the local namespace
	 * @throws tao_install_utils_Exception
	 * @param string $file the path to the RDF file
	 * @return boolean true if inserted
	 */
	public function insertLocalModelFile($file){
		if(!file_exists($file) || !is_readable($file)){
			throw new tao_install_utils_Exception("Unable to load ontology : $file");
		}
		return $this->insertLocalModel(file_get_contents($file));
	}

	/**
	 * Insert a model into the local namespace
	 * @param string $model the XML data
	 * @return boolean true if inserted
	 */
	public function insertLocalModel($model){
		$model = str_replace('LOCAL_NAMESPACE#', $this->localNs, $model);
		$model = str_replace('{ROOT_PATH}', ROOT_PATH, $model);
		return $this->insertModel($this->localNs, $model);
	}

	/**
	 * Insert a model
	 * @throws tao_install_utils_Exception
	 * @param string $file the path to the RDF file
	 * @return boolean true if inserted
	 */
	public function insertModelFile($namespace, $file){
		if(!file_exists($file) || !is_readable($file)){
			throw new tao_install_utils_Exception("Unable to load ontology : $file");
		}
		return $this->insertModel($namespace, file_get_contents($file));
	}

	/**
	 * Insert a model
	 * @param string $model the XML data
	 * @return boolean true if inserted
	 */
	public function insertModel($namespace, $model){

		$returnValue = false;
		if(!preg_match("/#$/", $namespace)){
		 	$namespace .= '#';
		 }

		 //rdf-api use ereg that are deprecated since PHP5.3
		 if (version_compare(phpversion(), '5.3.0', '>=')) {
			error_reporting(E_ALL & ~E_DEPRECATED);
		}

		$memModel 	= ModelFactory::getMemModel($namespace);
		$dbModel	= ModelFactory::getDefaultDbModel($namespace);
		$dbModel->getDbConn()->execute("SET NAMES 'UTF8'");

		// Load and parse the model
		$memModel->loadFromString($model, 'rdf');
		//$memModel->load($model);

		$added = 0;

		$it = $memModel->getStatementIterator();
		$size = $memModel->size();
		while ($it->hasNext()) {
			$statement = $it->next();
			if($dbModel->add($statement, 'generis') === true){
				$added++;
			}
		}

        if($size > 0 && $added > 0){
			$returnValue = true;
        }

        error_reporting(E_ALL);

        return $returnValue;
	}

	/**
	 * Conveniance method to get the list of models to install from the extensions
	 * @param array $simpleExtensions array of common_ext_SimpleExtension
	 * @return array of ns => files (array)
	 */
	public static function getModelsFromExtensions(array $simpleExtensions){
		$models = array();
		foreach($simpleExtensions as $extension){
			if(!$extension instanceof common_ext_SimpleExtension){
				throw new tao_install_utils_Exception("{$extension} is not a common_ext_SimpleExtension");
			}

			if(isset($extension->installFiles['rdf'])){
				$rdfFiles = $extension->installFiles['rdf'];
				//$rdfFiles : array of structure (ns, file)
				foreach($rdfFiles as $struct){
					foreach($extension->model as $model){
						if($model == $struct['ns']){
							if (!isset($models[$struct['ns']])) $models[$struct['ns']] = array();
							$models[$struct['ns']][] = $struct['file'];
							break;
						}
					}
				}
			}
		}
		return $models;
	}

	/**
	 * Convenience method to get the models to install from extension's locales.
	 * @param common_ext_SimpleExtension a common_ext_SimpleExtension instance.
	 * @return array of ns=>file
	 */
	public static function getTranslationModelsFromExtension(common_ext_SimpleExtension $simpleExtension) {
		$models = array();
		$extensionPath = dirname(__FILE__) . '/../../../' . $simpleExtension->id;
		$localesPath = $extensionPath . '/locales';

		// Get the target model.
		if (!isset($simpleExtension->model) || empty($simpleExtension->model)) {
			throw new tao_install_utils_Exception("No ontology target model for extension '" . $simpleExtension->name . "'.");
		}

		$extModel = $simpleExtension->model;
		if (is_array($extModel)) {
			$extModel = $extModel[0];
		}

		if (@is_dir($localesPath) && is_readable($localesPath)) {
			// Locales directory exists and is readable.
			$directories = scandir($localesPath);

			if ($directories !== false) {

				foreach ($directories as $dir) {
					if ($dir[0] != '.') {
						// Let's scan each language directory to find the messages.rdf file.
						$files = scandir($localesPath . '/' . $dir);

						if ($files !== false) {

							foreach ($files as $file) {
								if ($file[0] != '.' && $file == 'messages.rdf') {

									// Add this file to the return results.
									if (!isset($models[$extModel])) {
										$models[$extModel] = array();
									}

									$models[$extModel][] = $localesPath . '/' . $dir . '/messages.rdf';
								}
							}
						} else {
							throw new tao_install_utils_Exception("Uable to list files from language directory ' ${dir}'.");
						}
					}
				}

			} else {
				throw new tao_install_utils_Exception("Unable to read 'locales' from extension '" . $simpleExtension->name . "'");
			}
		} else {
			throw new tao_install_utils_Exception("Cannot read 'locales' directory in extension '" . $simpleExtension->name . "'.");
		}

		return $models;
	}
}
?>