<?php

error_reporting(E_ALL);

/**
 * The TaoTranslate script aims at providing command line tools to manage
 * of tao. It enables you to manage the i18n of the messages found in the source
 * (gettext) but also i18n of RDF Models.
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
 * The TaoTranslate script aims at providing command line tools to manage
 * of tao. It enables you to manage the i18n of the messages found in the source
 * (gettext) but also i18n of RDF Models.
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

    /**
     * Short description of attribute DEF_LANG_FILENAME
     *
     * @access public
     * @var string
     */
    const DEF_LANG_FILENAME = 'lang.rdf';

    // --- OPERATIONS ---

    /**
     * Things that must happen before script execution.
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
        							'deleteall',
                                    'enable',
                                    'disable');
        	
        	if (!in_array($this->options['action'], $allowedActions)) {
        		self::err("Please enter a valid 'action' parameter.", true);
        	} else {
        		// The 'action' parameter is ok.
        		// Let's check additional inputs depending on the value of the 'action' parameter.
        		$this->checkInput();	
        	}
        }
        // section -64--88-1-7-6b37e1cc:1336002dd1f:-8000:0000000000003287 end
    }

    /**
     * Main script implementation.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return void
     */
    public function run()
    {
        // section -64--88-1-7-6b37e1cc:1336002dd1f:-8000:0000000000003289 begin
        
        // Select the action to perform depending on the 'action' parameter.
        // Verification of the value of 'action' performed in self::preRun().
        switch ($this->options['action']) {
        	case 'create':
				$this->actionCreate();
        	break;
        	
        	case 'update':
        		$this->actionUpdate();
        	break;
        	
        	case 'updateall':
        		$this->actionUpdateAll();
        	break;
        	
        	case 'delete':
        		$this->actionDelete();
        	break;
        	
        	case 'deleteall':
        		$this->actionDeleteAll();
        	break;
            
            case 'enable':
                $this->actionEnable();
            break;
            
            case 'disable':
                $this->actionDisable();
            break;
        }
        // section -64--88-1-7-6b37e1cc:1336002dd1f:-8000:0000000000003289 end
    }

    /**
     * Things that must happen after the run() method.
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
     * Checks the inputs for the current script call.
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
        	
        	case 'updateall':
        		$this->checkUpdateAllInput();
        	break;
        	
        	case 'delete':
        		$this->checkDeleteInput();
        	break;
        	
        	case 'deleteall':
        		$this->checkDeleteAllInput();
        	break;
            
            case 'enable':
                $this->checkEnableInput();
            break;
            
            case 'disable':
                $this->checkDisableInput();
            break;
        	
        	default:
        		// Should not happen.
        		self::err("Fatal error while checking input parameters. Unknown 'action'.", true);
        	break;
        }
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:0000000000003840 end
    }

    /**
     * Checks the inputs for the 'create' action.
     *
     * @access private
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return void
     */
    private function checkCreateInput()
    {
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:0000000000003842 begin
        $defaults = array('language' => null,
                          'languageLabel' => null,
        				  'extension' => null,
        				  'input' => dirname(__FILE__) . '/../../' . $this->options['extension'] . '/' . self::DEF_INPUT_DIR,
        				  'output' => dirname(__FILE__) . '/../../' . $this->options['extension'] . '/' . self::DEF_OUTPUT_DIR,
        				  'build' => true, // Build translation files by having a look in source code, models.
        				  'force' => false); // Do not force rebuild if locale already exist.
        
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
     * Checks the inputs for the 'update' action.
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
	        			} else if (!is_readable($languageDir . '/' . self::DEF_PO_FILENAME)) {
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
     * checks the input for the 'updateAll' action.
     *
     * @access private
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return void
     */
    private function checkUpdateAllInput()
    {
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:0000000000003846 begin
        $defaults = array('input' => dirname(__FILE__) . '/../../' . $this->options['extension'] . '/' . self::DEF_INPUT_DIR,
        				  'output' => dirname(__FILE__) . '/../../' . $this->options['extension'] . '/' . self::DEF_OUTPUT_DIR,
        				  'extension' => null);
        
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
     * Checks the inputs for the 'delete' action.
     *
     * @access private
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return void
     */
    private function checkDeleteInput()
    {
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:0000000000003848 begin
        $defaults = array('language' => null,
        				  'input' => dirname(__FILE__) . '/../../' . $this->options['extension'] . '/' . self::DEF_INPUT_DIR,
        				  'output' => dirname(__FILE__) . '/../../' . $this->options['extension'] . '/' . self::DEF_OUTPUT_DIR,
                          'extension' => null);
        
        $this->options = array_merge($defaults, $this->options);
        
        if (is_null($this->options['extension'])){
        	self::err("Please provide an 'extension' identifier.", true);
        }else{
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
        }
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:0000000000003848 end
    }

    /**
     * Checks inputs for the 'deleteAll' action.
     *
     * @access private
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return void
     */
    private function checkDeleteAllInput()
    {
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:000000000000384A begin
     	$defaults = array('input' => dirname(__FILE__) . '/../../' . $this->options['extension'] . '/' . self::DEF_INPUT_DIR,
     					  'output' => dirname(__FILE__) . '/../../' . $this->options['extension'] . '/' . self::DEF_OUTPUT_DIR,
                          'extension' => null);
        
        $this->options = array_merge($defaults, $this->options);
        
    	// The input 'parameter' is optional.
    	if (!is_null($this->options['extension'])){
            if (!is_null($this->options['input'])) {
            	if (!is_dir($this->options['input'])) {
            		self::err("The 'input' parameter you provided is not a directory.", true);
            	} else if (!is_readable($this->options['input'])) {
            		self::err("The 'input' directory is not readable.", true);
            	}
            }
        }else{
            self::err("Please provide an 'extension' identifier.", true);
        }
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:000000000000384A end
    }

    /**
     * Implementation of the 'create' action.
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
        	self::err("The 'language' " . $this->options['language'] . " already exists in the file system. Use the 'force' parameter to overwrite it.", true);
        }
        
        // If we are still here... it means that we have to create the language directory.
        if (!$dirExists && !@mkdir($dir)) {
        	self::err("Unable to create 'language' directory '" . $this->options['language'] . "'.", true);	
        } else {
        	if ($this->options['build'] == true) {
        	    $tuCount = 0;
                $sortingMethod = tao_helpers_translation_TranslationFile::SORT_ASC_I;
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
	        	
	        	$manifestExtractor = new tao_helpers_translation_ManifestExtractor(array($this->options['input'] . '/actions'));
	        	$manifestExtractor->extract();
	        	
	        	$translationFile = new tao_helpers_translation_POFile();
                $translationFile->setSourceLanguage(tao_helpers_translation_Utils::getDefaultLanguage());
                $translationFile->setTargetLanguage($this->options['language']);
	        	$translationFile->addTranslationUnits($sourceExtractor->getTranslationUnits());
	        	$translationFile->addTranslationUnits($manifestExtractor->getTranslationUnits());
	        	
	        	// If it is the TAO Extension, get all TUs from all manifest files found
	        	// in other extensions. I do not like it but we need it because some translations related
	        	// to extensions are handled by the TAO extensions (e.g. Action panel in the BackOffice GUI).
	        	if ($this->options['extension'] == 'tao') {
	        		$this->addManifestsTranslations($translationFile);	
	        	}
	        	
	        	$sortedTus = $translationFile->sortBySource($sortingMethod);
	        	
	        	$sortedTranslationFile = new tao_helpers_translation_POFile();
                $sortedTranslationFile->setSourceLanguage(tao_helpers_translation_Utils::getDefaultLanguage());
                $sortedTranslationFile->setTargetLanguage($this->options['language']);
	        	$sortedTranslationFile->addTranslationUnits($sortedTus);
	        	$this->preparePOFile($sortedTranslationFile);
	        	
                $poPath = $dir . '/' . self::DEF_PO_FILENAME;
	        	$writer = new tao_helpers_translation_POFileWriter($poPath,
	        													   $sortedTranslationFile);
	        	$writer->write();
                
                $poCount = count($sortedTranslationFile->getTranslationUnits());
                $tuCount += $poCount;
                $this->outVerbose("PO Translation file '" . basename($poPath) . "' in '" . $this->options['language'] . "' created for extension '" . $this->options['extension'] . "(${poCount} messages)");
                
                $jsPath = $dir . '/' . self::DEF_JS_FILENAME;
	        	$writer = new tao_helpers_translation_JSFileWriter($jsPath,
	        													   $sortedTranslationFile);
                                                                   
                // JS Translation file must contain the tao meta-extension's messages.
                if ($this->options['extension'] !== 'tao'){
                    $taoMessagesPath = ROOT_PATH . '/tao/locales/' . $this->options['language'] . '/' . self::DEF_PO_FILENAME;
                    if (file_exists($taoMessagesPath)) {
	                    $poReader = new tao_helpers_translation_POFileReader($taoMessagesPath);
	                    $poReader->read();
	                    
	                    $rawTus = $sortedTranslationFile->getTranslationUnits();
	                    $sortedTranslationFile->setTranslationUnits($poReader->getTranslationFile()->getTranslationUnits());
	                    $sortedTranslationFile->addTranslationUnits($rawTus);
	                    $sortedTranslationFile->setTranslationUnits($sortedTranslationFile->sortBySource($sortingMethod));
	                    $this->outVerbose("Added tao meta-extension messages for language '" . $this->options['language'] . "'.");
                    } else {
	                    $this->outVerbose("Could not find tao meta-extension messages for language '" . $this->options['language'] . "'.");
                    }
                }
                                                                   
	        	$writer->write();
                
                $jsCount = count($sortedTranslationFile->getTranslationUnits());
                $this->outVerbose("JS Translation file '" . basename($jsPath) . "' in '" . $this->options['language'] . "' created for extension '" . $this->options['extension'] . "(${jsCount} messages)");
                
                // Now that PO files & JS files are created, we can create the translation models
                // if we find RDF models to load for this extension.
                $modelFiles = $this->getOntologyFiles($this->options['extension']);
                $translatableProperties = array(RDFS_LABEL, RDFS_COMMENT);
                
                foreach ($modelFiles as $ns => $files){
                    foreach ($files as $f){
                        $modelExtractor = new tao_helpers_translation_RDFExtractor(array($f));
                        $modelExtractor->setTranslatableProperties($translatableProperties);
                        $modelExtractor->extract();
                        
                        $rdfTranslationFile = new tao_helpers_translation_RDFTranslationFile();
                        $rdfTranslationFile->setSourceLanguage(tao_helpers_translation_Utils::getDefaultLanguage());
                        $rdfTranslationFile->setTargetLanguage($this->options['language']);
                        $rdfTranslationFile->addTranslationUnits($modelExtractor->getTranslationUnits());
                        $rdfTranslationFile->setExtensionId($this->options['extension']);
                        $rdfTranslationFile->setBase($ns);
                        
                        $writer = new tao_helpers_translation_RDFFileWriter($dir . '/' . basename($f),
                                                                            $rdfTranslationFile); 
                        $writer->write();
                        $rdfCount = count($rdfTranslationFile->getTranslationUnits());
                        $tuCount += $rdfCount;
                        
                        $this->outVerbose("RDF Translation model '" . basename($f) . "' in '" . $this->options['language'] . "' created for extension '" . $this->options['extension'] . "' (${rdfCount} messages).");      
                    }
                }
	        	
	        	$this->outVerbose("Language '" . $this->options['language'] . "' created for extension '" . $this->options['extension'] . "' " .
	        					  "(${tuCount} messages).");
        	} else {
        		// Only build virgin files.
        		// (Like a virgin... woot !)
        		$translationFile = new tao_helpers_translation_POFile();
                $translationFile->setSourceLanguage(tao_helpers_translation_Utils::getDefaultLanguage());
                $translationFile->setTargetLanguage($this->options['language']);
        		$this->preparePOFile($translationFile);
        		
        		$writer = new tao_helpers_translation_POFileWriter($dir . '/' . self::DEF_PO_FILENAME,
        														   $translationFile);
        		$writer->write();
        		$writer = new tao_helpers_translation_JSFileWriter($dir . '/' . self::DEF_JS_FILENAME,
        														   $translationFile);
        		$writer->write();
                
                $modelFiles = $this->getOntologyFiles($this->options['extension']);
                foreach ($modelFiles as $ns => $files){
                    foreach ($files as $f){
                        $translationFile = new tao_helpers_translation_RDFTranslationFile();
                        $translationFile->setSourceLanguage(tao_helpers_translation_Utils::getDefaultLanguage());
                        $translationFile->setTargetLanguage($this->options['language']);
                        $translationFile->setExtensionId($this->options['extension']);
                        $translationFile->setBase($ns);
                        
                        $writer = new tao_helpers_translation_RDFFileWriter($dir . '/' . basename($f),
                                                                            $translationFile);
                        $writer->write();
                    }
                }
                
        		$this->outVerbose("Language '" . $this->options['language'] . "' created for extension '" . $this->options['extension'] . "'.");
        	}
            
            // Create the language manifest in RDF.
            if ($this->options['extension'] == 'tao'){
                $langDescription = tao_helpers_translation_RDFUtils::createLanguageDescription($this->options['language'],
                                                                                               $this->options['languageLabel']);
                $langDescription->save($dir . '/' . self::DEF_LANG_FILENAME);
            }
        }
        // section 10-13-1-85-4f86d2fb:134b3339b70:-8000:0000000000003864 end
    }

    /**
     * Implementation of the 'update' action.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return void
     */
    public function actionUpdate()
    {
        // section 10-13-1-85-4f86d2fb:134b3339b70:-8000:0000000000003866 begin
        $this->outVerbose("Updating language '" . $this->options['language'] . "' for extension '" . $this->options['extension'] . "' ...");
    	$tuCount = 0;
        $sortingMethod = tao_helpers_translation_TranslationFile::SORT_ASC_I;
        
       	// Get virgin translations from the source code and manifest.
       	$filePaths = array($this->options['input'] . '/actions',
	        			   $this->options['input'] . '/helpers',
	        			   $this->options['input'] . '/models',
	        			   $this->options['input'] . '/views');
       	$extensions = array('php', 'tpl', 'js', 'ejs');
       	$sourceCodeExtractor = new tao_helpers_translation_SourceCodeExtractor($filePaths, $extensions);
       	$manifestExtractor = new tao_helpers_translation_ManifestExtractor(array($this->options['input'] . '/actions'));
       	$sourceCodeExtractor->extract();
       	$manifestExtractor->extract();
    	
       	$translationFile = new tao_helpers_translation_POFile();
        $translationFile->setSourceLanguage(tao_helpers_translation_Utils::getDefaultLanguage());
        $translationFile->setTargetLanguage($this->options['language']);
       	$translationFile->addTranslationUnits($sourceCodeExtractor->getTranslationUnits());
       	$translationFile->addTranslationUnits($manifestExtractor->getTranslationUnits());
       	
       	// If it is TAO extension, get TUs from all manifests.
       	if ($this->options['extension'] == 'tao') {
       		$this->addManifestsTranslations($translationFile);
       	}
       	
       	// For each TU that was recovered, have a look in an older version
       	// of the translations.
       	$oldFilePath = $this->buildLanguagePath($this->options['extension'], $this->options['language']) . '/' .self::DEF_PO_FILENAME;
       	$translationFileReader = new tao_helpers_translation_POFileReader($oldFilePath);
       	$translationFileReader->read();
       	$oldTranslationFile = $translationFileReader->getTranslationFile();
       	
       	foreach ($oldTranslationFile->getTranslationUnits() as $oldTu) {
       		if ($translationFile->hasSameSource($oldTu)) {
       			// No duplicates in TFs so I simply add it whatever happens.
       			// If it already has the same one, it means we will update it.
       			$translationFile->addTranslationUnit($oldTu);
       		}
       	}
       	
       	$sortedTranslationFile = new tao_helpers_translation_POFile();
        $sortedTranslationFile->setSourceLanguage($translationFile->getSourceLanguage());
        $sortedTranslationFile->setTargetLanguage($translationFile->getTargetLanguage());
       	$sortedTranslationFile->addTranslationUnits($translationFile->sortBySource($sortingMethod));
       	$this->preparePOFile($sortedTranslationFile);
       	
       	// Remove old files.
       	$oldJsFilePath = $this->buildLanguagePath($this->options['extension'], $this->options['language']) . '/' . self::DEF_JS_FILENAME;
       	tao_helpers_File::remove($oldFilePath);
       	tao_helpers_File::remove($oldJsFilePath);
       	
       	// Write the new ones.
       	$poFileWriter = new tao_helpers_translation_POFileWriter($oldFilePath, $sortedTranslationFile);
       	$poFileWriter->write();
        $poCount = count($sortedTranslationFile->getTranslationUnits());
        $tuCount += $poCount;
        $this->outVerbose("PO translation file '" . $this->options['language'] . "' updated for extension '" . $this->options['extension'] . "' (${poCount} messages).");
        
        // JS Files must be compiled with tao meta-extension messages.
        if ($this->options['extension'] !== 'tao'){
            $taoMessagesPath = ROOT_PATH . '/tao/locales/' . $this->options['language'] . '/' . self::DEF_PO_FILENAME;
            
            if (file_exists($taoMessagesPath)){
                $poReader = new tao_helpers_translation_POFileReader($taoMessagesPath);
                $poReader->read();
                
                $rawTus = $sortedTranslationFile->getTranslationUnits();
                $sortedTranslationFile->setTranslationUnits($poReader->getTranslationFile()->getTranslationUnits());
                $sortedTranslationFile->addTranslationUnits($rawTus);
                $sortedTranslationFile->setTranslationUnits($sortedTranslationFile->sortBySource($sortingMethod));
                $this->outVerbose("Added tao meta-extension messages for language '" . $this->options['language'] . "'.");
            } else {
                $this->outVerbose("Could not find tao meta-extension messages for language '" . $this->options['language'] . "'.");
            }
        }
        
        $jsFileWriter = new tao_helpers_translation_JSFileWriter($oldJsFilePath, $sortedTranslationFile);
       	$jsFileWriter->write();
        $jsCount = count($sortedTranslationFile->getTranslationUnits());
        $this->outVerbose("JS translation file '" . $this->options['language'] . "' updated for extension '" . $this->options['extension'] . "' (${jsCount} messages).");
        
        // We now deal with RDF models.
        $models = $this->getOntologyFiles($this->options['extension']);
        foreach ($models as $ns => $files){
            foreach ($files as $f){
                // Loop on 'master' models.
                $rdfExtractor = new tao_helpers_translation_RDFExtractor(array($f));
                $rdfExtractor->addTranslatableProperty('http://www.w3.org/2000/01/rdf-schema#label');
                $rdfExtractor->addTranslatableProperty('http://www.w3.org/2000/01/rdf-schema#comment');
                $rdfExtractor->extract();
                
                // The master RDF file is the ontology itself but non-translated that we find in /extId/models/ontology.
                $masterRDFFile = new tao_helpers_translation_RDFTranslationFile();
                $masterRDFFile->setSourceLanguage(tao_helpers_translation_Utils::getDefaultLanguage());
                $masterRDFFile->setTargetLanguage($this->options['language']);
                $masterRDFFile->addTranslationUnits($rdfExtractor->getTranslationUnits());
                $masterRDFFile->setBase($ns);
                $masterRDFFile->setExtensionId($this->options['extension']);
                
                // The slave RDF file is the translation of the ontology that we find in /extId/Locales/langCode.
                $slaveRDFFilePath = $this->buildLanguagePath($this->options['extension'], $this->options['language']) . '/' . basename($f);
                if (file_exists($slaveRDFFilePath)){
                    // Read the existing RDF Translation file for this RDF model.
                    $rdfReader = new tao_helpers_translation_RDFFileReader($slaveRDFFilePath);
                    $rdfReader->read();
                    $slaveRDFFile = $rdfReader->getTranslationFile();
                    
                    // Try to update translation units found in the master RDF file with
                    // targets found in the old translation of the ontology.
                    foreach ($slaveRDFFile->getTranslationUnits() as $oTu){
                        if ($masterRDFFile->hasSameSource($oTu)){
                            $masterRDFFile->addTranslationUnit($oTu);
                        }
                    }
                    
                    // Remove Slave RDF file. It will be overriden by the
                    // modified Master RDF file.
                    tao_helpers_File::remove($slaveRDFFilePath);
                }
                
                // Write Master RDF file as the new Slave RDF file.
                $rdfWriter = new tao_helpers_translation_RDFFileWriter($slaveRDFFilePath, $masterRDFFile);
                $rdfWriter->write();
                $rdfCount = count($masterRDFFile->getTranslationUnits());
                $tuCount += $rdfCount;
                $this->outVerbose("RDF Translation model '" . basename($f) . "' in '" . $this->options['language'] . "' updated for extension '" . $this->options['extension'] . "' (${rdfCount} messages).");
            }
        }
        
       	$this->outVerbose("Language '" . $this->options['language'] . "' updated for extension '" . $this->options['extension'] . "' (${tuCount} messages).");
        // section 10-13-1-85-4f86d2fb:134b3339b70:-8000:0000000000003866 end
    }

    /**
     * Implementation of the 'updateAll' action.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return void
     */
    public function actionUpdateAll()
    {
        // section 10-13-1-85-4f86d2fb:134b3339b70:-8000:0000000000003868 begin
        // Scan the locales folder for languages in the wwextension and
        // launch actionUpdate for each of them.

    	// Get the list of languages that will be updated.
    	$rootDir = dirname(__FILE__) . '/../..';
    	$extensionDir = $rootDir . '/' . $this->options['extension'];
    	$localesDir = $extensionDir . '/locales';
    	$locales = array();
    	
    	$directories = scandir($localesDir);
    	if ($directories === false) {
    		self::err("The locales directory of extension '" . $this->options['extension'] . "' cannot be read.", true);	
    	} else {
    		foreach ($directories as $dir) {
    			if ($dir[0] !== '.') {
    				// It is a language directory.
    				$locales[] = $dir;
    			}
    		}
    	}
    	
    	// We now identified locales to be updated.
    	$this->outVerbose("Languages '" . implode(',', $locales) . "' will be updated for extension '" . $this->options['extension'] . "'.");
    	foreach ($locales as $l) {
    		$this->options['language'] = $l;
    		$this->checkUpdateInput();
    		$this->actionUpdate();
    		
    		$this->outVerbose("");
    	}
        // section 10-13-1-85-4f86d2fb:134b3339b70:-8000:0000000000003868 end
    }

    /**
     * Implementation of the 'delete' action.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return void
     */
    public function actionDelete()
    {
        // section 10-13-1-85-4f86d2fb:134b3339b70:-8000:000000000000386A begin
        $this->outVerbose("Deleting language '" . $this->options['language'] . "' for extension '" . $this->options['extension'] . "' ...");
    	
    	$dir = $this->buildLanguagePath($this->options['extension'], $this->options['language']);
        if (!tao_helpers_File::remove($dir, true)) {
        	self::err("Could not delete language '" . $this->options['language'] . "' for extension '" . $this->options['extension'] . "'.", true);	
        }
        
        $this->outVerbose("Language '" . $this->options['language'] . "' for extension '" . $this->options['extension'] . "' successfuly deleted.");
        // section 10-13-1-85-4f86d2fb:134b3339b70:-8000:000000000000386A end
    }

    /**
     * Implementation of the 'deleteAll' action.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return void
     */
    public function actionDeleteAll()
    {
        // section 10-13-1-85-4f86d2fb:134b3339b70:-8000:000000000000386C begin
    	// Get the list of languages that will be deleted.
    	$this->outVerbose("Deleting all languages for extension '" . $this->options['extension'] . "' ...");
    	
    	$rootDir = dirname(__FILE__) . '/../..';
    	$extensionDir = $rootDir . '/' . $this->options['extension'];
    	$localesDir = $extensionDir . '/locales';
    	$locales = array();
    	
    	$directories = scandir($localesDir);
    	if ($directories === false) {
    		self::err("The locales directory of extension '" . $this->options['extension'] . "' cannot be read.", true);	
    	} else {
    		foreach ($directories as $dir) {
    			if ($dir[0] !== '.') {
    				// It is a language directory.
    				$locales[] = $dir;
    			}
    		}
    	}
    	
    	foreach ($locales as $l) {
    		$this->options['language'] = $dir;
    		$this->checkDeleteInput();
    		$this->actionDelete();
    		
    		$this->outVerbose("");
    	}
        // section 10-13-1-85-4f86d2fb:134b3339b70:-8000:000000000000386C end
    }

    /**
     * Builds the path to files dedicated to a given language (locale) for a
     * extension ID.
     *
     * @access private
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string extension
     * @param  string language
     * @return string
     */
    private function buildLanguagePath($extension, $language)
    {
        $returnValue = (string) '';

        // section 10-13-1-85-6cb6330f:134b35c8bda:-8000:0000000000003870 begin
        if (isset($this->options['extension']) && !is_null($this->options['extension'])){
            $returnValue = $this->options['output'] . '/' . $language;
        }else{
            $returnValue = dirname(__FILE__) . '/../../' . $extension . '/' . self::DEF_OUTPUT_DIR . '/' . $language;
        }
        // section 10-13-1-85-6cb6330f:134b35c8bda:-8000:0000000000003870 end

        return (string) $returnValue;
    }

    /**
     * Find a structure.xml manifest in a given directory.
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
     * Prepare a PO file before output by adding headers to it.
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
     * Determines if an given directory actually contains a TAO extension.
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
        $hasStructure = $this->findStructureManifest($directory) !== false;
        $hasPHPManifest = false;
        
        $files = scandir($this->options['input']);
        if ($files !== false) {
        	foreach ($files as $f) {
				if (is_file($this->options['input'] . '/' .$f) && is_readable($this->options['input'] . '/' . $f)) {
					if ($f == 'manifest.php') {
						$hasPHPManifest = true;
					}
				}
        	}
        }
        
        $returnValue = $hasStructure || $hasPHPManifest;
        // section 10-13-1-85--228d4509:134d1864dda:-8000:00000000000038E3 end

        return (bool) $returnValue;
    }

    /**
     * Add translations as translation units found in a structure.xml manifest
     * a given PO file.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  POFile poFile
     * @return void
     */
    public function addManifestsTranslations( tao_helpers_translation_POFile $poFile)
    {
        // section 10-13-1-85--228d4509:134d1864dda:-8000:00000000000038E5 begin
        $this->outVerbose("Adding all manifests messages to extension '" . $this->options['extension'] . "'");
    	
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
							
							$this->outVerbose("Manifest of extension '" . $dir . "' added to extension '" . $this->options['extension'] . "' (" . $newTranslationsCount . " messages).");
						}
					}
				}
        	}
        }
        // section 10-13-1-85--228d4509:134d1864dda:-8000:00000000000038E5 end
    }

    /**
     * Add the requested language in the ontology. It will used the parameters
     * the command line for logic.
     *
     * @access protected
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return void
     */
    protected function addLanguageToOntology()
    {
        // section 10-13-1-85-59c88e8f:13543d8a458:-8000:0000000000003A88 begin
        $this->outVerbose("Importing RDF language description '" . $this->options['language'] . "' to ontology...");
        
        // RDF Language Descriptions are stored in the tao meta-extension locales.
        $expectedDescriptionPath = $this->buildLanguagePath('tao', $this->options['language']) . '/lang.rdf';
        
        if (file_exists($expectedDescriptionPath)){
            if (is_readable($expectedDescriptionPath)){
                
                // Let's remove any instance of the language description before inserting the new one.
                $taoNS = 'http://www.tao.lu/Ontologies/TAO.rdf#';
                $expectedLangUri = $taoNS . 'Lang' . $this->options['language'];
                $lgDescription = new core_kernel_classes_Resource($expectedLangUri);
                
                if ($lgDescription->exists()){
                    $lgDescription->delete();
                    $this->outVerbose("Existing RDF Description language '" . $this->options['language'] . "' deleted.");
                }
                
                $generisAdapterRdf = new tao_helpers_data_GenerisAdapterRdf();
                if (true === $generisAdapterRdf->import($expectedDescriptionPath, null, LOCAL_NAMESPACE)){
                    $this->outVerbose("RDF language description '" . $this->options['language'] . "' successfully imported.");
                }else{
                    self::err("An error occured while importing the RDF language description '" . $this->options['language'] . "'.", true);
                }
                
            }else{
                self::err("RDF language description (lang.rdf) cannot be read in meta-extension 'tao' for language '" . $this->options['language'] . "'.", true);
            }
        }else{
            self::err("RDF language description (lang.rdf) not found in meta-extension 'tao' for language '" . $this->options['language'] . "'.", true);
        }
        
        
        $this->outVerbose("RDF language description '" . $this->options['language'] . "' added to ontology.");
        // section 10-13-1-85-59c88e8f:13543d8a458:-8000:0000000000003A88 end
    }

    /**
     * Removes a language from the Ontology and all triples with the related
     * tag. Will use command line parameters for logic.
     *
     * @access protected
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return void
     */
    protected function removeLanguageFromOntology()
    {
        // section 10-13-1-85-59c88e8f:13543d8a458:-8000:0000000000003A8E begin
        $this->outVerbose("Removing RDF language description '" . $this->options['language'] . "' from ontology...");
        $taoNS = 'http://www.tao.lu/Ontologies/TAO.rdf#';
        $expectedDescriptionUri = $taoNS . 'Lang' . $this->options['language'];
        $lgResource = new core_kernel_classes_Resource($expectedDescriptionUri);
        
        if (true === $lgResource->exists()) {
            $lgResource->delete();
            $this->outVerbose("RDF language description '" . $this->options['language'] . "' successfully removed.");
        }else{
            $this->outVerbose("RDF language description '" . $this->options['language'] . "' not found but considered removed.");
        }
        // section 10-13-1-85-59c88e8f:13543d8a458:-8000:0000000000003A8E end
    }

    /**
     * Checks authentication parameters for the TAO API.
     *
     * @access protected
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return void
     */
    protected function checkAuthInput()
    {
        // section 127-0-1-1-3599ab3f:135546c24af:-8000:0000000000003704 begin
        $defaults = array('user' => null,
						  'password' => null);
						  
		$this->options = array_merge($defaults, $this->options);
		
		if ($this->options['user'] == null) {
			$this->err("Please provide a value for the 'user' parameter.", true);
		}
		else if ($this->options['password'] == null) {
			$this->err("Please provide a value for the 'password' parameter.", true);
		}
        // section 127-0-1-1-3599ab3f:135546c24af:-8000:0000000000003704 end
    }

    /**
     * Get the ontology file paths for a given extension, sorted by target name
     *
     * @access private
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string extension The extension where you want to find the related ontology files (.rdf).
     * @return array
     */
    private function getOntologyFiles($extension)
    {
        $returnValue = array();

        // section -64--88-56-1--acd0dae:136abeb190f:-8000:000000000000390D begin
        $ext = new common_ext_SimpleExtension($extension);
        if (isset($ext->installFiles['rdf'])){
            foreach ($ext->installFiles['rdf'] as $f){
                $ns = $f['ns'];
                
                if (!isset($returnValue[$ns])){
                    $returnValue[$ns] = array();
                }
                
                $returnValue[$ns][] = $f['file'];
            }
        }
        // section -64--88-56-1--acd0dae:136abeb190f:-8000:000000000000390D end

        return (array) $returnValue;
    }

    /**
     * Check inputs for the 'enable' action.
     *
     * @access private
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return void
     */
    private function checkEnableInput()
    {
        // section -64--88-56-1-218fa982:136eddd6b1c:-8000:000000000000399C begin
        $this->checkAuthInput();
        $defaults = array('language' => null,
                          'input' => dirname(__FILE__) . '/../../' . $this->options['extension'] . '/' . self::DEF_INPUT_DIR,
                          'output' => dirname(__FILE__) . '/../../' . $this->options['extension'] . '/' . self::DEF_OUTPUT_DIR);
                          
        $this->options = array_merge($defaults, $this->options);
        
        if ($this->options['language'] == null){
            self::err("Please provide the 'language' parameter.", true);
        }
        // section -64--88-56-1-218fa982:136eddd6b1c:-8000:000000000000399C end
    }

    /**
     * Short description of method checkDisableInput
     *
     * @access private
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return void
     */
    private function checkDisableInput()
    {
        // section -64--88-56-1-218fa982:136eddd6b1c:-8000:00000000000039B5 begin
        $this->checkAuthInput();
        $defaults = array('language' => null);
        $this->options = array_merge($defaults, $this->options);
        if ($this->options['language'] == null){
            self::err("Please provide the 'language' parameter.", true);
        }
        // section -64--88-56-1-218fa982:136eddd6b1c:-8000:00000000000039B5 end
    }

    /**
     * Short description of method actionEnable
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return void
     */
    public function actionEnable()
    {
        // section -64--88-56-1-218fa982:136eddd6b1c:-8000:00000000000039B8 begin
        $userService = tao_models_classes_UserService::singleton();
        $this->outVerbose("Connecting to TAO as '" . $this->options['user'] . "' ...");
        if ($userService->loginUser($this->options['user'], md5($this->options['password']))){
            $this->outVerbose("Connected to TAO as '" . $this->options['user'] . "'.");
            $this->addLanguageToOntology();
            $userService->logout();
            $this->outVerbose("Disconnected from TAO.");
        }else{
            self::err("Unable to connect to TAO as '" . $this->options['user'] . "'. Please check user name and password.", true);
        }
        // section -64--88-56-1-218fa982:136eddd6b1c:-8000:00000000000039B8 end
    }

    /**
     * Implementation of the 'disable' action. When this action is called, a
     * Language Description ('language' param) is removed from the Knowledge
     * The language is at this time not available anymore to end-users. However,
     * Triples that had a corresponding language tag are not remove from the
     * If the language is enabled again via the 'enable' action of this script,
     * having corresponding languages will be reachable again.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return void
     */
    public function actionDisable()
    {
        // section -64--88-56-1-218fa982:136eddd6b1c:-8000:00000000000039BB begin
        $userService = tao_models_classes_UserService::singleton();
        $this->outVerbose("Connecting to TAO as '" . $this->options['user'] . "' ...");
        if ($userService->loginUser($this->options['user'], md5($this->options['password']))){
            $this->outVerbose("Connected to TAO as '" . $this->options['user'] . "'.");
            $this->removeLanguageFromOntology();
            $userService->logout();
            $this->outVerbose("Disconnected from TAO.");
        }else{
            self::err("Unable to connect to TAO as '" . $this->options['user'] . "'. Please check user name and password.", true);
        }
        // section -64--88-56-1-218fa982:136eddd6b1c:-8000:00000000000039BB end
    }

} /* end of class tao_scripts_TaoTranslate */

?>