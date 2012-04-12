<?php

error_reporting(E_ALL);

/**
 * This class aims at providing utility methods for handling RDF files for
 * and internationalization.
 *
 * @author Jerome Bogaerts
 * @package tao
 * @since 2.2
 * @subpackage helpers_translation
 * @version 1.0
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section -64--88-56-1-52e480ce:136a58c45e6:-8000:00000000000038F7-includes begin
// section -64--88-56-1-52e480ce:136a58c45e6:-8000:00000000000038F7-includes end

/* user defined constants */
// section -64--88-56-1-52e480ce:136a58c45e6:-8000:00000000000038F7-constants begin
// section -64--88-56-1-52e480ce:136a58c45e6:-8000:00000000000038F7-constants end

/**
 * This class aims at providing utility methods for handling RDF files for
 * and internationalization.
 *
 * @access public
 * @author Jerome Bogaerts
 * @package tao
 * @since 2.2
 * @subpackage helpers_translation
 * @version 1.0
 */
class tao_helpers_translation_RDFUtils
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Creates a language description file for TAO using the RDF-XML language.
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  string code The language code e.g. fr-FR.
     * @param  string label The language label e.g. French in english.
     * @return DomDocument
     */
    public static function createLanguageDescription($code, $label)
    {
        $returnValue = null;

        // section -64--88-56-1-52e480ce:136a58c45e6:-8000:00000000000038FC begin
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
        $labelNode->setAttributeNS($xmlNs, 'xml:lang', $code);
        $labelNode->appendChild($doc->createCDATASection($label));
        $descriptionNode->appendChild($labelNode);
        
        $valueNode = $doc->CreateElementNS($rdfNs, 'rdf:value');
        $valueNode->appendChild($doc->createCDATASection($code));
        $descriptionNode->appendChild($valueNode);
        
        $returnValue = $doc;
        // section -64--88-56-1-52e480ce:136a58c45e6:-8000:00000000000038FC end

        return $returnValue;
    }

} /* end of class tao_helpers_translation_RDFUtils */

?>