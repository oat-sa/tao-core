<?php

error_reporting(E_ALL);

/**
 * TAO - tao\scripts\class.TaoTranslate.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 12.01.2012, 14:15:26 with ArgoUML PHP module 
 * (last revised $Date: 2008-04-19 08:22:08 +0200 (Sat, 19 Apr 2008) $)
 *
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 * @subpackage scripts
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_scripts_Runner
 *
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
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
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
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
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
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
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
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
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
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
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
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
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return void
     */
    private function checkCreateInput()
    {
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:0000000000003842 begin
        $defaults = array('language' => null,
        				  'extension' => null,
        				  'input' => dirname(__FILE__) . '/../../' . $this->options['extension'] . '/' . self::DEF_INPUT_DIR,
        				  'output' => dirname(__FILE__) . '/../../' . $this->options['extension'] . '/' . self::DEF_OUTPUT_DIR,
        				  'build' => true,
        				  'force' => false);
        
        $this->options = array_merge($defaults, $this->options);
    	
    	if (is_null($this->options['language'])) {
        	self::err("Please provide a 'language' identifier such as en-US, fr-CA, IT, ...", true);
        } else {
        	if (is_null($this->options['extension'])) {
        		self::err("Please provide an 'extension' for which the 'language' will be created", true);
        	} else {
        		// Check if the extension exists.
        		$extensionDir = dirname(__FILE__) . '/../../' . $this->options['extension'];
        		if (!is_dir($extensionDir)) {
        			self::err("The extension '" . $this->options['extension'] . "' does not exist.", true);
        		} else if (!is_readable($extensionDir)) {
        			self::err("The '" . $this->options['extension'] . "' directory is not readable. Please check permissions on this directory.", true);
        		} else if (!is_writable($extensionDir)) {
        			self::err("The '" . $this->options['extension'] . "' directory is not writable. Please check permissions on this directory.", true);
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
        	}
        }
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:0000000000003842 end
    }

    /**
     * Short description of method checkUpdateInput
     *
     * @access private
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return void
     */
    private function checkUpdateInput()
    {
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:0000000000003844 begin
        $defaults = array('language' => null,
        				  'extension' => null,
        				  'input' => dirname(__FILE__) . '/../../' . $this->options['extension'] . '/' . self::DEF_INPUT_DIR,
        				  'output' => dirname(__FILE__) . '/../../' . $this->options['extension'] . '/' . self::DEF_OUTPUT_DIR);
        
        $this->options = array_merge($defaults, $this->options);
        
        if (is_null($this->options['language'])) {
        	self::err("Please provide a 'language' identifier such as en-US, fr-CA, IT, ...", true);
        } else {
        	// Check if the language folder exists and is readable/writable.
        	$languageDir = $this->buildLanguagePath($this->options['extension'], $this->options['language']);
        	if (!is_dir($languageDir)) {
        		self::err("The 'language' directory ${languageDir} does not exist.", true);
        	} else if (!is_readable($languageDir)) {
        		self::err("The 'language' directory ${languageDir} is not readable. Please check permissions on this directory.");	
        	} else if (!is_writable($languageDir)) {
        		self::err("The 'language' directory ${languageDir} is not writable. Please check permissions on this directory.");	
        	} else {
	        	if (is_null($this->options['extension'])) {
	        		self::err("Please provide an 'extension' for which the 'language' will be created", true);
	        	} else {
	        		// Check if the extension exists.
	        		$extensionDir = dirname(__FILE__) . '/../../' . $this->options['extension'];
	        		if (!is_dir($extensionDir)) {
	        			self::err("The extension '" . $this->options['extension'] . "' does not exist.", true);
	        		} else if (!is_readable($extensionDir)) {
	        			self::err("The '" . $this->options['extension'] . "' directory is not readable. Please check permissions on this directory.", true);
	        		} else if (!is_writable($extensionDir)) {
	        			self::err("The '" . $this->options['extension'] . "' directory is not writable. Please check permissions on this directory.", true);
	        		} else {
	        			
	        			// And can we read the messages.po file ?
	        			if (!file_exists($languageDir . '/' . self::DEF_PO_FILENAME)) {
	        				self::err("Cannot find " . self::DEF_PO_FILENAME . " for extension '" . $this->options['extension'] . "' and language '" . $this->options['language'] . "'.", true);	
	        			} else if (!is_readable($languadeDir . '/' . self::DEF_PO_FILENAME)) {
	        				self::err(self::DEF_PO_FILENAME . " is not readable for '" . $this->options['extension'] . "' and language '" . $this->options['language'] . "'. Please check permissions for this file." , true);
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
	        		}
	        	}
        	}
        	
        	
        }
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:0000000000003844 end
    }

    /**
     * Short description of method checkUpdateAllInput
     *
     * @access private
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
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
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
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
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
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
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
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
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
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
	        	
	        	$translationFile = new tao_helpers_translation_POFile('en-US', $this->options['language']);
	        	$translationFile->addTranslationUnits($sourceExtractor->getTranslationUnits());
	        	$translationFile->addTranslationUnits($manifestExtractor->getTranslationUnits());
	        	
	        	// If it is the TAO Extension, get all TUs from all manifest files found
	        	// in other extensions. I do not like it but we need it because some translations related
	        	// to extensions are handled by the TAO extensions (e.g. Action panel in the BackOffice GUI).
	        	if ($this->options['extension'] == 'tao') {
	        		$this->addManifestsTranslations($translationFile);	
	        	}
	        	
	        	$sortedTus = $translationFile->sortBySource(tao_helpers_translation_TranslationFile::SORT_ASC_I);
	        	
	        	$sortedTranslationFile = new tao_helpers_translation_POFile('en-US', $this->options['language']);
	        	$sortedTranslationFile->addTranslationUnits($sortedTus);
	        	$this->preparePOFile($sortedTranslationFile);
	        	
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
        		$translationFile = new tao_helpers_translation_POFile('en-US', $this->options['language']);
        		$this->preparePOFile($translationFile);
        		
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
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return void
     */
    public function actionUpdate()
    {
        // section 10-13-1-85-4f86d2fb:134b3339b70:-8000:0000000000003866 begin
       	// Get virgin translations from the source code and manifest.
       	$filePaths = array($this->options['input'] . '/actions',
	        			   $this->options['input'] . '/helpers',
	        			   $this->options['input'] . '/models',
	        			   $this->options['input'] . '/views');
       	$extensions = array('php', 'tpl', 'js', 'ejs');
       	$sourceCodeExtractor = new tao_helpers_translation_SourceCodeExtractor($filePaths, $extensions);
       	$manifestExtractor = new tao_helpers_translation_ManifestExtractor(array($this->options['input'] . 'actions'));
       	$sourceCodeExtractor->extract();
       	$manifestExtractor->extract();
    	
       	$translationFile = new tao_helpers_translation_POFile('en-US', $this->options['language']);
       	$translationFile->addTranslationUnits($sourceCodeExtractor->getTranslationUnits());
       	$translationFile->addTranslationUnits($manifestExtractor->getTranslationUnits());
       	
       	// If it is TAO extension, get TUs from all manifests.
       	if ($this->options['extension'] == 'tao') {
       		$this->addManifestsTranslations($translationFile);
       	}
       	
       	// For each TU that was recovered, have a look in an older version
       	// of the translations.
       	$oldFilePath = $this->buildLanguagePath($this->options['extension'], $this->options['language']);
       	$translationFileReader = new tao_helpers_translation_POFileReader($oldFilePath);
       	$translationFileReader->read();
       	$oldTranslationFile = $translationFileReader->getTranslationFile();
       	
       	foreach ($translationFile->getTranslationUnits() as $tu) {
       		if ($oldTranslationFile->hasSameSource($tu)) {
       			
       		}
       	}
       	
        // section 10-13-1-85-4f86d2fb:134b3339b70:-8000:0000000000003866 end
    }

    /**
     * Short description of method actionUpdateAll
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
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
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
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
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
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
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
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
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string directory
     * @return mixed
     */
    public function findStructureManifest($directory = null)
    {
        $returnValue = null;

        // section 10-13-1-85-49a3b43f:134b39b4ede:-8000:0000000000003874 begin
        if ($directory == null) {
        	$actionsDir = $this->options['input'] . '/actions';	
        } else {
        	$actionsDir = $directory . '/actions';
        }
        
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

    /**
     * Short description of method preparePOFile
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  POFile poFile
     * @return void
     */
    public function preparePOFile( tao_helpers_translation_POFile $poFile)
    {
        // section 10-13-1-85-73c9aa2d:134d14a8b30:-8000:00000000000038C3 begin
        $poFile->addHeader('Project-Id-Version', PRODUCT_NAME . ' ' . TAO_VERSION_NAME);
        $poFile->addHeader('PO-Revision-Date', date('Y-m-d') . 'T' . date('H:i:s'));
        $poFile->addHeader('Last-Translator', 'TAO Translation Team <translation@tao.lu>');
        $poFile->addHeader('MIME-Version', '1.0');
        $poFile->addHeader('Language', $poFile->getTargetLanguage());
        $poFile->addHeader('Content-Type', 'text/plain; charset=utf-8');
        $poFile->addHeader('Content-Transfer-Encoding', '8bit');
        // section 10-13-1-85-73c9aa2d:134d14a8b30:-8000:00000000000038C3 end
    }

    /**
     * Short description of method isExtension
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string directory
     * @return boolean
     */
    public function isExtension($directory)
    {
        $returnValue = (bool) false;

        // section 10-13-1-85--228d4509:134d1864dda:-8000:00000000000038E3 begin
        $returnValue = $this->findStructureManifest($directory) !== false;
        // section 10-13-1-85--228d4509:134d1864dda:-8000:00000000000038E3 end

        return (bool) $returnValue;
    }

    /**
     * Short description of method addManifestsTranslations
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  POFile poFile
     * @return void
     */
    public function addManifestsTranslations( tao_helpers_translation_POFile $poFile)
    {
        // section 10-13-1-85--228d4509:134d1864dda:-8000:00000000000038E5 begin
        $this->outVerbose("Adding all manifests entries to extension '" . $this->options['extension'] . "'");
    	
        $rootDir = dirname(__FILE__) . '/../../';
        $directories = scandir($rootDir);
        $exceptions = array('generis', 'tao', '.*');
        
        if (false === $directories) {
        	self::err("The TAO root directory is not readable. Please check permissions on this directory.", true);	
        } else {
        	foreach ($directories as $dir) {
				if (is_dir($rootDir . $dir) && !in_array($dir, $exceptions)) {
					// Maybe it should be read.
					if (in_array('.*', $exceptions) && $dir[0] == '.') {
						continue;	
					} else {
						// Is this a TAO extension ?
						if (self::isExtension($rootDir . $dir)) {							
							$manifestReader = new tao_helpers_translation_ManifestExtractor(array($rootDir . $dir . '/actions'));
							$manifestReader->extract();
							$newTranslationsCount = count($manifestReader->getTranslationUnits());
							$poFile->addTranslationUnits($manifestReader->getTranslationUnits());
							
							$this->outVerbose("Manifest of extension '" . $dir . "' added to extension '" . $this->options['extension'] . "' (" . $newTranslationsCount . " entries).");
						}
					}
				}
        	}
        }
        // section 10-13-1-85--228d4509:134d1864dda:-8000:00000000000038E5 end
    }

    /**
     * Short description of method addLanguageOntology
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string languageName
     * @return boolean
     */
    public function addLanguageOntology($languageName)
    {
        $returnValue = (bool) false;

        // section 10-13-1-85--228d4509:134d1864dda:-8000:00000000000038E8 begin
        throw new tao_helpers_translation_TranslationException("Not yet implemented.");
        // section 10-13-1-85--228d4509:134d1864dda:-8000:00000000000038E8 end

        return (bool) $returnValue;
    }

} /* end of class tao_scripts_TaoTranslate */

?>