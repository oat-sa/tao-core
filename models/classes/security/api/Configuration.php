<?php

namespace oat\tao\model\security\api;


/**
 * Interface for security related configuration
 *
 * @author Martijn Swinkels <m.swinkels@taotesting.com>
 */
interface Configuration
{
    const SECURITY_CONFIGURATION = 'http://www.tao.lu/Ontologies/TAO.rdf#SecurityConfig';
    const CSP_HEADER = 'http://www.tao.lu/Ontologies/TAO.rdf#CspHeaders';


    /**
     * Get the current configuration value for the given property
     *
     * @param string $propertyUri
     * @return mixed
     */
    public function get($propertyUri);

    /**
     * Set a configuration value for the given property.
     *
     * @param string $propertyUri
     * @param mixed $value
     */
    public function set($propertyUri, $value);
}