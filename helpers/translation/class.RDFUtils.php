<?php

error_reporting(E_ALL);

/**
 * Aims at providing utility methods for RDF Translation models.
 *
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 * @subpackage helpers_translation
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section -64--88-56-1--5deb8f54:136cf746d4c:-8000:0000000000003939-includes begin
// section -64--88-56-1--5deb8f54:136cf746d4c:-8000:0000000000003939-includes end

/* user defined constants */
// section -64--88-56-1--5deb8f54:136cf746d4c:-8000:0000000000003939-constants begin
// section -64--88-56-1--5deb8f54:136cf746d4c:-8000:0000000000003939-constants end

/**
 * Aims at providing utility methods for RDF Translation models.
 *
 * @access public
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 * @subpackage helpers_translation
 */
class tao_helpers_translation_RDFUtils
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Unserialize an RDFTranslationUnit annotation and returns an associative
     * where keys are annotation names, and values are the annotation values.
     * Throws TranslationException.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string annotations The annotations string.
     * @return array
     */
    public static function unserializeAnnotations($annotations)
    {
        $returnValue = array();

        // section -64--88-56-1--5deb8f54:136cf746d4c:-8000:000000000000393A begin
        $reg = "/\s*@(subject|predicate|sourceLanguage|targetLanguage|source)[\t ]+(.+)(?:\s*|$)/u";
        $matches = array();
        if (false !== preg_match_all($reg, $annotations, $matches)){
            // No problems with $reg.
            if (isset($matches[1])){
                // We got some annotations.
                for ($i = 0; $i < count($matches[1]); $i++){
                    // Annotation name $i processing. Do we have a value for it?
                    $name = $matches[1][$i];
                    if (isset($matches[2][$i])){
                        // We have an annotation with a name and a value.
                        $value = $matches[2][$i];
                        $returnValue[$name] = $value;
                    }
                }
            }
        }else{
            throw new tao_helpers_translation_TranslationException("A fatal error occured while parsing annotations '${annotations}.'");
        }
        // section -64--88-56-1--5deb8f54:136cf746d4c:-8000:000000000000393A end

        return (array) $returnValue;
    }

    /**
     * Serializes an associative array of annotations where keys are annotation
     * and values are annotation values.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  array annotations An associative array that represents a collection of annotations, where keys are the annotation names and values the annotation values.
     * @param  string glue Indicates what is the glue between serialized annotations.
     * @return string
     */
    public static function serializeAnnotations($annotations, $glue = '')
    {
        $returnValue = (string) '';

        // section -64--88-56-1--5deb8f54:136cf746d4c:-8000:0000000000003941 begin
        // Set default glue.
        if ($glue == ''){
            $glue = "\n    ";
        }
        
        $a = array();
        foreach ($annotations as $n => $v){
            $a[] = '@' . trim($n) . " ${v}";
        }
        $returnValue = implode($glue, $a);
        // section -64--88-56-1--5deb8f54:136cf746d4c:-8000:0000000000003941 end

        return (string) $returnValue;
    }

    /**
     * Creates a language description file for TAO using the RDF-XML language.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string code string code The language code e.g. fr-FR.
     * @param  string label string label The language label e.g. French in english.
     * @return DomDocument
     */
    public static function createLanguageDescription($code, $label)
    {
        $returnValue = null;

        // section -64--88-56-1--791b33a3:136e8a84490:-8000:000000000000398B begin
        $languageType = CLASS_LANGUAGES;
        $languagePrefix = 'http://www.tao.lu/Ontologies/TAO.rdf#Lang';
        $rdfNs = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
        $rdfsNs = 'http://www.w3.org/2000/01/rdf-schema#';
        $xmlNs = 'http://www.w3.org/XML/1998/namespace';
        $xmlnsNs = 'http://www.w3.org/2000/xmlns/';
        $base = 'http://www.tao.lu/Ontologies/TAO.rdf#';
        
        $doc = new DomDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;
        
        $rdfNode = $doc->createElementNS($rdfNs, 'rdf:RDF');
        $rdfNode->setAttributeNS($xmlNs, 'xml:base', $base);
        $doc->appendChild($rdfNode);
        
        $descriptionNode = $doc->createElementNS($rdfNs, 'rdf:Description');
        $descriptionNode->setAttributeNS($rdfNs, 'rdf:about', $languagePrefix . $code);
        $rdfNode->appendChild($descriptionNode);
        
        $typeNode = $doc->createElementNS($rdfNs, 'rdf:type');
        $typeNode->setAttributeNS($rdfNs, 'rdf:resource', $languageType);
        $descriptionNode->appendChild($typeNode);
        
        $labelNode = $doc->createElementNS($rdfsNs, 'rdfs:label');
        $labelNode->setAttributeNS($xmlNs, 'xml:lang', 'EN');
        $labelNode->appendChild($doc->createCDATASection($label));
        $descriptionNode->appendChild($labelNode);
        
        $valueNode = $doc->CreateElementNS($rdfNs, 'rdf:value');
        $valueNode->appendChild($doc->createCDATASection($code));
        $descriptionNode->appendChild($valueNode);
        
        $returnValue = $doc;
        // section -64--88-56-1--791b33a3:136e8a84490:-8000:000000000000398B end

        return $returnValue;
    }

} /* end of class tao_helpers_translation_RDFUtils */

?>