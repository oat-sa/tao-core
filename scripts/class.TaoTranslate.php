<?php

error_reporting(E_ALL);

/**
 * TAO - tao\scripts\class.TaoTranslate.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 11.01.2012, 13:42:49 with ArgoUML PHP module 
 * (last revised $Date: 2008-04-19 08:22:08 +0200 (Sat, 19 Apr 2008) $)
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
// section -64--88-1-7-6b37e1cc:1336002dd1f:-8000:0000000000003286-includes begin
// section -64--88-1-7-6b37e1cc:1336002dd1f:-8000:0000000000003286-includes end

/* user defined constants */
// section -64--88-1-7-6b37e1cc:1336002dd1f:-8000:0000000000003286-constants begin
// section -64--88-1-7-6b37e1cc:1336002dd1f:-8000:0000000000003286-constants end

/**
 * Short description of class tao_scripts_TaoTranslate
 *
 * @access public
 * @author firstname and lastname of author, <author@example.org>
 * @package tao
 * @subpackage scripts
 */
class tao_scripts_TaoTranslate
    extends tao_scripts_Runner
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute DEF_INPUT_DIR
     *
     * @access public
     * @var string
     */
    const DEF_INPUT_DIR = '.';

    /**
     * Short description of attribute DEF_OUTPUT_DIR
     *
     * @access public
     * @var string
     */
    const DEF_OUTPUT_DIR = 'locales';

    /**
     * Short description of attribute DEF_PO_FILENAME
     *
     * @access public
     * @var string
     */
    const DEF_PO_FILENAME = 'messages.po';

    /**
     * Short description of attribute DEF_JS_FILENAME
     *
     * @access public
     * @var string
     */
    const DEF_JS_FILENAME = 'messages_po.js';

    /**
     * Short description of attribute options
     *
     * @access protected
     * @var array
     */
    protected $options = array();

    // --- OPERATIONS ---

    /**
     * Short description of method preRun
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return void
     */
    public function preRun()
    {
        // section -64--88-1-7-6b37e1cc:1336002dd1f:-8000:0000000000003287 begin
    	$this->options = array('verbose' => false,
        					   'action' => null,
    						   'extension' => null);
        
        $this->options = array_merge($this->options, $this->parameters);
        
        if ($this->options['verbose'] == true) {
        	$this->verbose = true;
        } else {
        	$this->verbose = false;
        }
        
        // The 'action' parameter is always required.
        if ($this->options['action'] == null) {
        	self::err("Please enter the 'action' parameter.", true);
        } else {
        	$this->options['action'] = strtolower($this->options['action']);
        	$allowedActions = array('create',
        							'update',
        							'delete',
        							'updateall',
        							'deleteall');
        	
        	if (!in_array($this->options['action'], $allowedActions)) {
        		self::err("Please enter a valid 'action' parameter.", true);
        	} else {
        		// The 'action' parameter is ok. But what about the 'extension' parameter?
        		if ($this->options['extension'] == null) {
        			self::err("Please enter the extension on wich the script will apply.", true);
        		} else {
        			// Everything is fine for the 'action' and 'extension' parameters.
        			// Let's check additional inputs depending on the value of the 'action' parameter.
        			$this->checkInput();	
        		}
        	}
        }
        // section -64--88-1-7-6b37e1cc:1336002dd1f:-8000:0000000000003287 end
    }

    /**
     * Short description of method run
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return void
     */
    public function run()
    {
        // section -64--88-1-7-6b37e1cc:1336002dd1f:-8000:0000000000003289 begin
//        $inputs = '';
//    	foreach ($this->options as $k => $o) {
//        	$inputs .= "${k}: \t $o\n";
//        }
//        $this->debug($inputs);
        
        
        // Select the action to perform depending on the 'action' parameter.
        // Verification of the value of 'action' performed in self::preRun().
        switch ($this->options['action']) {
        	case 'create':
				$this->actionCreate();
        	break;
        	
        	case 'update':
        		$this->actionUpdate();
        	break;
        	
        	case 'updateAll':
        		$this->actionUpdateAll();
        	break;
        	
        	case 'delete':
        		$this->actionDelete();
        	break;
        	
        	case 'deleteAll':
        		$this->actionDeleteAll();
        	break;
        }
        // section -64--88-1-7-6b37e1cc:1336002dd1f:-8000:0000000000003289 end
    }

    /**
     * Short description of method postRun
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return void
     */
    public function postRun()
    {
        // section -64--88-1-7-6b37e1cc:1336002dd1f:-8000:000000000000328B begin
        // section -64--88-1-7-6b37e1cc:1336002dd1f:-8000:000000000000328B end
    }

    /**
     * Short description of method checkInput
     *
     * @access private
     * @author firstname and lastname of author, <author@example.org>
     * @return void
     */
    private function checkInput()
    {
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:0000000000003840 begin
        switch ($this->options['action']) {
        	case 'create':
        		$this->checkCreateInput();
        	break;
        	
        	case 'update':
        		$this->checkUpdateInput();
        	break;
        	
        	case 'updateAll':
        		$this->checkUpdateAllInput();
        	break;
        	
        	case 'delete':
        		$this->checkDeleteInput();
        	break;
        	
        	case 'deleteAll':
        		$this->checkDeleteAllInput();
        	break;
        	
        	default:
        		// Should not happen.
        		self::err("Fatal error while checking input parameters. Unknown 'action'.", true);
        	break;
        }
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:0000000000003840 end
    }

    /**
     * Short description of method checkCreateInput
     *
     * @access private
     * @author firstname and lastname of author, <author@example.org>
     * @return void
     */
    private function checkCreateInput()
    {
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:0000000000003842 begin
        $defaults = array('language' => null,
        				  'input' => dirname(__FILE__) . '/../../' . $this->options['extension'] . '/' . self::DEF_INPUT_DIR,
        				  'output' => dirname(__FILE__) . '/../../' . $this->options['extension'] . '/' . self::DEF_OUTPUT_DIR,
        				  'build' => true,
        				  'force' => false);
        
        $this->options = array_merge($defaults, $this->options);
    	
    	if (is_null($this->options['language'])) {
        	self::err("Please provide a 'language' identifier such as en-US, fr-CA, IT, ...", true);
        } else {
        	// The input 'parameter' is optional.
        	// (and only used if the 'build' parameter is set to true)
        	if (!is_null($this->options['input'])) {
        		if (!is_dir($this->options['input'])) {
        			self::err("The 'input' parameter you provided is not a directory.", true);
        		} else if (!is_readable($this->options['input'])) {
        			self::err("The 'input' directory is not readable.", true);
        		}
        	}
        	
        	// The 'output' parameter is optional.
        	if (!is_null($this->options['output'])) {
        		if (!is_dir($this->options['output'])) {
        			self::err("The 'output' parameter you provided is not a directory.", true);
        		} else if (!is_writable($this->options['output'])) {
        			self::err("The 'output' directory is not writable.", true);
        		}
        	}
        }
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:0000000000003842 end
    }

    /**
     * Short description of method checkUpdateInput
     *
     * @access private
     * @author firstname and lastname of author, <author@example.org>
     * @return void
     */
    private function checkUpdateInput()
    {
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:0000000000003844 begin
        $defaults = array('language' => null,
        				  'input' => dirname(__FILE__) . '/../../' . $this->options['extension'] . '/' . self::DEF_INPUT_DIR,
        				  'output' => dirname(__FILE__) . '/../../' . $this->options['extension'] . '/' . self::DEF_OUTPUT_DIR);
        
        $this->options = array_merge($defaults, $this->options);
        
        if (is_null($this->options['language'])) {
        	self::err("Please provide a 'language' identifier such as en-US, fr-CA, IT, ...", true);
        } else {
        	// The input 'parameter' is optional.
        	if (!is_null($this->options['input'])) {
        		if (!is_dir($this->options['input'])) {
        			self::err("The 'input' parameter you provided is not a directory.", true);
        		} else if (!is_readable($this->options['input'])) {
        			self::err("The 'input' directory is not readable.", true);
        		}
        	}
        	
        	// The output 'parameter' is optional.
        	if (!is_null($this->options['output'])) {
        		if (!is_dir($this->options['output'])) {
        			self::err("The 'output' parameter you provided is not a directory.", true);
        		} else if (!is_writable($this->options['output'])) {
        			self::err("The 'output' directory is not writable.", true);
        		}
        	}
        }
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:0000000000003844 end
    }

    /**
     * Short description of method checkUpdateAllInput
     *
     * @access private
     * @author firstname and lastname of author, <author@example.org>
     * @return void
     */
    private function checkUpdateAllInput()
    {
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:0000000000003846 begin
        $defaults = array('input' => dirname(__FILE__) . '/../../' . $this->options['extension'] . '/' . self::DEF_INPUT_DIR,
        				  'output' => dirname(__FILE__) . '/../../' . $this->options['extension'] . '/' . self::DEF_OUTPUT_DIR);
        
        $this->options = array_merge($defaults, $this->options);
        
    	// The input 'parameter' is optional.
        if (!is_null($this->options['input'])) {
        	if (!is_dir($this->options['input'])) {
        		self::err("The 'input' parameter you provided is not a directory.", true);
        	} else if (!is_readable($this->options['input'])) {
        		self::err("The 'input' directory is not readable.", true);
        	}
        }
        
        // The output 'parameter' is optional.
        if (!is_null($this->options['output'])) {
        	if (!is_dir($this->options['output'])) {
        		self::err("The 'output' parameter you provided is not a directory.", true);
        	} else if (!is_writable($this->options['output'])) {
        		self::err("The 'output' directory is not writable.", true);
        	}
        }
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:0000000000003846 end
    }

    /**
     * Short description of method checkDeleteInput
     *
     * @access private
     * @author firstname and lastname of author, <author@example.org>
     * @return void
     */
    private function checkDeleteInput()
    {
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:0000000000003848 begin
        $defaults = array('language' => null,
        				  'input' => dirname(__FILE__) . '/../../' . $this->options['extension'] . '/' . self::DEF_INPUT_DIR);
        
        $this->options = array_merge($defaults, $this->options);
        
    	if (is_null($this->options['language'])) {
        	self::err("Please provide a 'language' identifier such as en-US, fr-CA, IT, ...", true);
        } else {
        	// The input 'parameter' is optional.
        	if (!is_null($this->options['input'])) {
        		if (!is_dir($this->options['input'])) {
        			self::err("The 'input' parameter you provided is not a directory.", true);
        		} else if (!is_readable($this->options['input'])) {
        			self::err("The 'input' directory is not readable.", true);
        		}
        	}
        }
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:0000000000003848 end
    }

    /**
     * Short description of method checkDeleteAllInput
     *
     * @access private
     * @author firstname and lastname of author, <author@example.org>
     * @return void
     */
    private function checkDeleteAllInput()
    {
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:000000000000384A begin
     	$defaults = array('input' => dirname(__FILE__) . '/../../' . $this->options['extension'] . '/' . self::DEF_INPUT_DIR);
        
        $this->options = array_merge($defaults, $this->options);
        
    	// The input 'parameter' is optional.
        if (!is_null($this->options['input'])) {
        	if (!is_dir($this->options['input'])) {
        		self::err("The 'input' parameter you provided is not a directory.", true);
        	} else if (!is_readable($this->options['input'])) {
        		self::err("The 'input' directory is not readable.", true);
        	}
        }
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:000000000000384A end
    }

    /**
     * Short description of method debug
     *
     * @access protected
     * @author firstname and lastname of author, <author@example.org>
     * @param  string string
     * @return void
     */
    protected function debug($string)
    {
        // section 10-13-1-85--13eeb565:134b31f90c6:-8000:0000000000003861 begin
        self::out($string);
        // section 10-13-1-85--13eeb565:134b31f90c6:-8000:0000000000003861 end
    }

    /**
     * Short description of method actionCreate
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return void
     */
    public function actionCreate()
    {
        // section 10-13-1-85-4f86d2fb:134b3339b70:-8000:0000000000003864 begin
        $this->outVerbose("Creating language '" . $this->options['language'] . "' for extension '" . $this->options['extension'] . "' ...");
    	
        // We first create the directory where locale files will go.
        $dir = $this->buildLanguagePath($this->options['extension'], $this->options['language']);
        $dirExists = false;
        
        if (file_exists($dir) && is_dir($dir) && $this->options['force'] == true) {
        	$dirExists = true;
        	$this->outVerbose("Language '" . $this->options['language'] . "' exists for extension '" . $this->options['extension'] . "'. " .
        					  "Creation will be forced.");
        	
        	// Clean it up.
        	foreach (scandir($dir) as $d) {
        		if ($d !== '.' && $d !== '..' && $d !== '.svn') {
		        	if (!tao_helpers_File::remove($dir . '/' . $d, true)) {
		        		self::err("Unable to clean up 'language' directory '" . $dir . "'.", true);
		        	}        			
        		}
        	}
        } else if (file_exists($dir) && is_dir($dir) && $this->options['force'] == false) {
        	self::err("The 'language' " . $this->options['language'] . " already exists.", true);
        }
        
        // If we are still here... it means that we have to create the language directory.
        if (!@mkdir($dir) && !$dirExists) {
        	self::err("Unable to create 'language' directory '" . $this->options['language'] . "'.", true);	
        } else {
        	if ($this->options['build'] == true) {
        		$this->outVerbose("Building language '" . $this->options['language'] . "' for extension '" . $this->options['extension'] . "' ...");
	        	// Let's populate the language with raw PO files containing sources but no targets.
	        	// Source code extraction.
	        	$fileExtensions = array('php', 'tpl', 'js', 'ejs');
	        	$filePaths = array($this->options['input'] . '/actions',
	        					   $this->options['input'] . '/helpers',
	        					   $this->options['input'] . '/models',
	        					   $this->options['input'] . '/views');
	        					   
	        	$sourceExtractor = new tao_helpers_translation_SourceCodeExtractor($filePaths, $fileExtensions);
	        	$sourceExtractor->extract();
	        	
	        	$manifestExtractor = new tao_helpers_translation_ManifestExtractor($this->options['input'] . '/actions');
	        	$manifestExtractor->extract();
	        	
	        	$translationFile = new tao_helpers_translation_TranslationFile('en-US', $this->options['language']);
	        	$translationFile->addTranslationUnits($sourceExtractor->getTranslationUnits());
	        	$translationFile->addTranslationUnits($manifestExtractor->getTranslationUnits());
	        	$sortedTus = $translationFile->sortBySource(tao_helpers_translation_TranslationFile::SORT_ASC_I);
	        	
	        	$sortedTranslationFile = new tao_helpers_translation_TranslationFile('en-US', $this->options['language']);
	        	$sortedTranslationFile->addTranslationUnits($sortedTus);
	        	
	        	$writer = new tao_helpers_translation_POFileWriter($dir . '/' . self::DEF_PO_FILENAME,
	        													   $sortedTranslationFile);
	        	$writer->write();
	        	$writer = new tao_helpers_translation_JSFileWriter($dir . '/' . self::DEF_JS_FILENAME,
	        													   $sortedTranslationFile);
	        	$writer->write();
	        	
	        	$this->outVerbose("Language '" . $this->options['language'] . "' created for extension '" . $this->options['extension'] . "' " .
	        					  "(" . count($sortedTranslationFile->getTranslationUnits()) . " entries).");
        	} else {
        		// Only build virgin files.
        		// (Like a virgin... woot !)
        		$translationFile = new tao_helpers_translation_TranslationFile('en-US', $this->options['language']);
        		$writer = new tao_helpers_translation_POFileWriter($dir . '/' . self::DEF_PO_FILENAME,
        														   $translationFile);
        		$writer->write();
        		$writer = new tao_helpers_translation_JSFileWriter($dir . '/' . self::DEF_JS_FILENAME,
        														   $translationFile);
        		$writer->write();
        		
        		$this->outVerbose("Language '" . $this->options['language'] . "' created for extension '" . $this->options['extension'] . "'.");
        	}
        	
        	
        }
        // section 10-13-1-85-4f86d2fb:134b3339b70:-8000:0000000000003864 end
    }

    /**
     * Short description of method actionUpdate
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return void
     */
    public function actionUpdate()
    {
        // section 10-13-1-85-4f86d2fb:134b3339b70:-8000:0000000000003866 begin
        // section 10-13-1-85-4f86d2fb:134b3339b70:-8000:0000000000003866 end
    }

    /**
     * Short description of method actionUpdateAll
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return void
     */
    public function actionUpdateAll()
    {
        // section 10-13-1-85-4f86d2fb:134b3339b70:-8000:0000000000003868 begin
        // section 10-13-1-85-4f86d2fb:134b3339b70:-8000:0000000000003868 end
    }

    /**
     * Short description of method actionDelete
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return void
     */
    public function actionDelete()
    {
        // section 10-13-1-85-4f86d2fb:134b3339b70:-8000:000000000000386A begin
        // section 10-13-1-85-4f86d2fb:134b3339b70:-8000:000000000000386A end
    }

    /**
     * Short description of method actionDeleteAll
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return void
     */
    public function actionDeleteAll()
    {
        // section 10-13-1-85-4f86d2fb:134b3339b70:-8000:000000000000386C begin
        // section 10-13-1-85-4f86d2fb:134b3339b70:-8000:000000000000386C end
    }

    /**
     * Short description of method buildLanguagePath
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  string extension
     * @param  string language
     * @return string
     */
    public function buildLanguagePath($extension, $language)
    {
        $returnValue = (string) '';

        // section 10-13-1-85-6cb6330f:134b35c8bda:-8000:0000000000003870 begin
        $returnValue = $this->options['output'] . '/' . $language;
        // section 10-13-1-85-6cb6330f:134b35c8bda:-8000:0000000000003870 end

        return (string) $returnValue;
    }

    /**
     * Short description of method findStructureManifest
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function findStructureManifest()
    {
        $returnValue = null;

        // section 10-13-1-85-49a3b43f:134b39b4ede:-8000:0000000000003874 begin
        $actionsDir = $this->options['input'] . '/actions';
        $dirEntries = scandir($actionsDir);
        
        if ($dirEntries === false) {
        	$returnValue = false;	
        } else {
        	$structureFile = null;
        	foreach ($dirEntries as $f) {
				if (preg_match("/(.*)structure\.xml$/", $f)) {
					$structureFile = $f;
					break;
				}
        	}
        	
        	if ($structureFile === null) {
        		$returnValue = false;
        	} else {
        		$returnValue = $structureFile;
        	}
        }
        // section 10-13-1-85-49a3b43f:134b39b4ede:-8000:0000000000003874 end

        return $returnValue;
    }

} /* end of class tao_scripts_TaoTranslate */

?>