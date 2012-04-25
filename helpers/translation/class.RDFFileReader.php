<?php

error_reporting(E_ALL);

/**
 * An implementation of TranslationFileReader aiming at reading RDF Translation
 *
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 * @subpackage helpers_translation
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * A Reading class for TranslationFiles. Must be implemented by a concrete class
 * a given Translation Format such as XLIFF, PO, ... The read method must be
 * by subclasses.
 *
 * @author Jerome Bogaerts
 * @since 2.2
 * @version 1.0
 */
require_once('tao/helpers/translation/class.TranslationFileReader.php');

/* user defined includes */
// section -64--88-56-1--508090e5:136c5d740c9:-8000:0000000000003920-includes begin
// section -64--88-56-1--508090e5:136c5d740c9:-8000:0000000000003920-includes end

/* user defined constants */
// section -64--88-56-1--508090e5:136c5d740c9:-8000:0000000000003920-constants begin
// section -64--88-56-1--508090e5:136c5d740c9:-8000:0000000000003920-constants end

/**
 * An implementation of TranslationFileReader aiming at reading RDF Translation
 *
 * @access public
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 * @subpackage helpers_translation
 */
class tao_helpers_translation_RDFFileReader
    extends tao_helpers_translation_TranslationFileReader
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method read
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return mixed
     */
    public function read()
    {
        // section -64--88-56-1--508090e5:136c5d740c9:-8000:0000000000003922 begin
        $translationUnits = array();
        
        try{
            $translationFile = $this->getTranslationFile();
        }
        catch (tao_helpers_translation_TranslationException $e){
            $translationFile = new tao_helpers_translation_RDFTranslationFile();
        }
        
        $this->setTranslationFile($translationFile);
        $inputFile = $this->getFilePath();
        
        if (file_exists($inputFile)){
            if (is_file($inputFile)){
                if (is_readable($inputFile)){
                    
                    try{
                        $doc = new DOMDocument('1.0', 'UTF-8');
                        $doc->load($inputFile);
                        $xpath = new DOMXPath($doc);
                        $rdfNS = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
                        $xmlNS = 'http://www.w3.org/XML/1998/namespace';
                        $xpath->registerNamespace('rdf', $rdfNS);
                        
                        $descriptions = $xpath->query('//rdf:Description');
                        foreach ($descriptions as $description){
                            if ($description->hasAttributeNS($rdfNS, 'about')){
                                $subject = $description->getAttributeNS($rdfNS, 'about');
                                
                                // Let's retrieve properties.
                                foreach ($description->childNodes as $property){
                                    if ($property->nodeType == XML_ELEMENT_NODE){
                                        // Retrieve namespace uri of this node.
                                        if ($property->namespaceURI != null){
                                            $predicate = $property->namespaceURI . $property->localName;
                                            
                                            // Retrieve an hypothetic target language.
                                            $lang = tao_helpers_translation_Utils::getDefaultLanguage();
                                            if ($property->hasAttributeNS($xmlNS, 'lang')){
                                                $lang = $property->getAttributeNS($xmlNS, 'lang');
                                            }
                                            
                                            $object = $property->nodeValue;
                                            
                                            $tu = new tao_helpers_translation_RDFTranslationUnit('');
                                            $tu->setTargetLanguage($lang);
                                            $tu->setTarget($object);
                                            $tu->setSubject($subject);
                                            $tu->setPredicate($predicate);
                                            
                                            // Try to get the sourceLanguage 
                                            
                                            $translationUnits[] = $tu;
                                        }
                                    }
                                }
                            }
                        }

                        $this->getTranslationFile()->addTranslationUnits($translationUnits);
                    }
                    catch (DOMException $e){
                        throw new tao_helpers_translation_TranslationException("'${inputFile}' cannot be parsed.");
                    }
                    
                }else{
                    throw new tao_helpers_translation_TranslationException("'${inputFile}' cannot be read. Check your system permissions.");
                }
            }else{
                throw new tao_helpers_translation_TranslationException("'${inputFile}' is not a file.");
            }
        }else{
            throw new tao_helpers_translation_TranslationException("The file '${inputFile}' does not exist.");
        }
        // section -64--88-56-1--508090e5:136c5d740c9:-8000:0000000000003922 end
    }

} /* end of class tao_helpers_translation_RDFFileReader */

?>