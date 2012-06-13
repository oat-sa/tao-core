<?php

error_reporting(E_ALL);

/**
 * TAO - tao/scripts/class.TaoPreparePublicActions.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 06.03.2012, 07:58:48 with ArgoUML PHP module
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author firstname and lastname of author, <author@example.org>
 * @package tao
 * @subpackage scripts
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_scripts_Runner
 *
 * @author firstname and lastname of author, <author@example.org>
 */
require_once('tao/scripts/class.Runner.php');

/* user defined includes */
// section 127-0-1-1--570b06ee:135e6b6b680:-8000:000000000000684A-includes begin
// section 127-0-1-1--570b06ee:135e6b6b680:-8000:000000000000684A-includes end

/* user defined constants */
// section 127-0-1-1--570b06ee:135e6b6b680:-8000:000000000000684A-constants begin
// section 127-0-1-1--570b06ee:135e6b6b680:-8000:000000000000684A-constants end

/**
 * Short description of class tao_scripts_TaoPreparePublicActions
 *
 * @access public
 * @author firstname and lastname of author, <author@example.org>
 * @package tao
 * @subpackage scripts
 */
class tao_scripts_TaoPreparePublicActions
    extends tao_scripts_Runner
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method preRun
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function preRun()
    {
        // section 127-0-1-1--570b06ee:135e6b6b680:-8000:000000000000684C begin
        // section 127-0-1-1--570b06ee:135e6b6b680:-8000:000000000000684C end
    }

    /**
     * Short description of method run
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function run()
    {
        // section 127-0-1-1--570b06ee:135e6b6b680:-8000:000000000000684E begin
		//Templates
		$rdf_header = file_get_contents(dirname(__FILE__).'/preparePublicActions/rdfHeader');
		$rdf_footer = file_get_contents(dirname(__FILE__).'/preparePublicActions/rdfFooter');
		$rdf_modtpl = file_get_contents(dirname(__FILE__).'/preparePublicActions/rdfModuleTemplate');
		$rdf_acttpl = file_get_contents(dirname(__FILE__).'/preparePublicActions/rdfActionTemplate');
		$rdf_taomanager = file_get_contents(dirname(__FILE__).'/preparePublicActions/rdfTaoManager');

		foreach (common_ext_ExtensionsManager::singleton()->getInstalledExtensions() as $extension) {
			if ($extension->id == 'generis') {
				continue;
			}
			common_Logger::i('preparing extension '.$extension->getID());
			//New RDF content
			$rdf = $rdf_header;
			foreach ($extension->getAllModules() as $module) {
				common_Logger::i(' preparing module '.$module);
				$moduleName = substr($module, strrpos($module, '_') +1);
				//Introspection, get public method
				try {
					$reflector = new ReflectionClass($module);
					$methods = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);
					if (count($methods)) {
						$rdft = '';
						foreach ($methods as $m) {
							if (!$m->isConstructor() && !$m->isDestructor() && is_subclass_of($m->class,'module') && $m->name != 'setView') {
								$rdft .= "\n".str_replace(
									array("{extension}", "{module}","{action}"),
									array($extension->id, $moduleName, $m->name),
									$rdf_acttpl);
							}
						}
						if ($rdft != '') {
							//Add only if has method
							$rdf .= "\n".str_replace(
									array("{extension}","{module}"),
									array($extension->id,$moduleName),
									$rdf_modtpl)."\n".$rdft;
							//$rdf .= "\n".str_replace("{base}", $dir, str_replace("{name}", $module, str_replace("{extension}", $dir, $rdf_modtpl)))."\n".$rdft;
							//Add taoManager rights
							$rdf .= "\n\n".str_replace("{uri}", FUNCACL_NS.'#m_'.$extension->id.'_'.$moduleName, $rdf_taomanager)."\n";
						}
					}
				}
				catch (ReflectionException $e) {
					echo $e->getLine().' : '.$e->getMessage()."\n";
				}
			}
			//Save the RDF
			$rdf .= "\n".$rdf_footer;
			file_put_contents(ROOT_PATH.$extension->id.'/models/ontology/funcacl.rdf', $rdf);
		}
		
		
		/*
		//From the root, look actions for all subdir
		$dirs = array_diff(scandir(ROOT_PATH), array('..', '.', '.svn', '.htaccess', 'generis'));
		foreach ($dirs as $dir) {
			if (is_dir(ROOT_PATH.$dir) && file_exists(ROOT_PATH.$dir.'/actions')) {
				//New RDF content
				$rdf = $rdf_header;

				$action_dirs = array_diff(scandir(ROOT_PATH.$dir.'/actions'), array('..', '.'));
				foreach ($action_dirs as $action) {
					if (is_file(ROOT_PATH.$dir.'/actions/'.$action) && substr($action, 0, 6) == "class." && substr($action, -4) == ".php") {
						//Add the module
						//http://www.tao.lu/Ontologies/taoFuncACL.rdf#m_{name}
						$module = substr($action, 6, -4);
						$ext = FUNCACL_NS."#m_".$dir.'_'.$module;
						$class = $dir.'_actions_'.$module;
						$topclass = 'Module';
						if (!is_subclass_of($class, $topclass)) {
							$this->out($class.' does not inherit '.$topclass);
						}

						//Introspection, get public method
						try {
							$reflector = new ReflectionClass($class);
							$methods = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);
							if (count($methods)) {
								$rdft = '';
								foreach ($methods as $m) {
									if (!$m->isConstructor() && !$m->isDestructor() && is_subclass_of($m->class,$topclass) && $m->name != 'setView') {
										$rdft .= "\n".str_replace("{base}", $dir.'_'.$module, str_replace("{name}", $m->name, str_replace("{member}", $ext, $rdf_acttpl)));
									}
								}
								if ($rdft != '') {
									//Add only if has method
									$rdf .= "\n".str_replace("{base}", $dir, str_replace("{name}", $module, str_replace("{extension}", $dir, $rdf_modtpl)))."\n".$rdft;
									//Add taoManager rights
									$rdf .= "\n\n".str_replace("{uri}", $ext, $rdf_taomanager)."\n";
								}
							}
						}
						catch (ReflectionException $e) {
							echo $e->getLine().' : '.$e->getMessage()."\n";
						}

						$rdf .= "\n";
					}
				}

				//Save the RDF
				$rdf .= "\n".$rdf_footer;
				file_put_contents(ROOT_PATH.$dir.'/models/ontology/funcacl.rdf', $rdf);
			}
		}
		*/
        // section 127-0-1-1--570b06ee:135e6b6b680:-8000:000000000000684E end
    }

    /**
     * Short description of method postRun
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function postRun()
    {
        // section 127-0-1-1--570b06ee:135e6b6b680:-8000:0000000000006850 begin
        // section 127-0-1-1--570b06ee:135e6b6b680:-8000:0000000000006850 end
    }

} /* end of class tao_scripts_TaoPreparePublicActions */

?>