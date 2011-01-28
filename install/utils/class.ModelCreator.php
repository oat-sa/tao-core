<?php
class tao_install_utils_ModelCreator{
	
	public function insertSuperUser(array $userData){
		
		if(!isset($userData['login']) || !isset($userData['password'])){
			var_dump($userData);
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
		
		echo "<pre>";
		echo htmlentities($doc->saveXML());
		echo "</pre>";
	}
}
?>