<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/class.Versionning.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 28.06.2012, 15:14:47 with ArgoUML PHP module
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Jehan Bihin
 * @package tao
 * @since 2.3
 * @subpackage helpers
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1-6b9a2186:1383319f00e:-8000:0000000000003B40-includes begin
// section 127-0-1-1-6b9a2186:1383319f00e:-8000:0000000000003B40-includes end

/* user defined constants */
// section 127-0-1-1-6b9a2186:1383319f00e:-8000:0000000000003B40-constants begin
// section 127-0-1-1-6b9a2186:1383319f00e:-8000:0000000000003B40-constants end

/**
 * Short description of class tao_helpers_Versionning
 *
 * @access public
 * @author Jehan Bihin
 * @package tao
 * @since 2.3
 * @subpackage helpers
 */
class tao_helpers_Versionning
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Initialize the versionning
     *
     * @access public
     * @author Jehan Bihin
     * @param  array constants
     * @return mixed
     * @since 2.3
     */
    public static function initialize($constants)
    {
        // section 127-0-1-1-6b9a2186:1383319f00e:-8000:0000000000003B41 begin
		//update the generis config file with the new constants
		$configWriter = new tao_install_utils_ConfigWriter(GENERIS_BASE_PATH.'/common/conf/default/versioning.conf.php', GENERIS_BASE_PATH.'/common/conf/versioning.conf.php');
		$configWriter->createConfig();
		$configWriter->writeConstants($constants);

		//Regarding to the versioning sytem type
		switch($constants['GENERIS_VERSIONED_REPOSITORY_TYPE']){
			case 'svn':
				$repositoryType = 'http://www.tao.lu/Ontologies/TAOItem.rdf#VersioningRepositoryTypeSubversion';
				break;
			default:
				//self::out("Unable to recognize the given type ".$constants['GENERIS_VERSIONED_REPOSITORY_TYPE'], array('color' => 'red'));
				throw new Exception("Unable to recognize the given type ".$constants['GENERIS_VERSIONED_REPOSITORY_TYPE']);
				return;
		}

		$repositoryExist = new core_kernel_classes_Resource('http://www.tao.lu/Ontologies/generis.rdf#DefaultRepository');
		if($repositoryExist->exists()){
			//self::out("Warning : The default repository (http://www.tao.lu/Ontologies/generis.rdf#DefaultRepository) exists ", array('color' => 'light_red'));
			//self::out("It will be replaced by the new one", array('color' => 'light_red'));
			common_Logger::w("The default repository (http://www.tao.lu/Ontologies/generis.rdf#DefaultRepository) exists ! It will be replaced by the new one.");
		}

		// Instantiate the repository in the ontology
		$repository = core_kernel_versioning_Repository::create(
			new core_kernel_classes_Resource($repositoryType),
			$constants['GENERIS_VERSIONED_REPOSITORY_URL'],
			$constants['GENERIS_VERSIONED_REPOSITORY_LOGIN'],
			$constants['GENERIS_VERSIONED_REPOSITORY_PASSWORD'],
			$constants['GENERIS_VERSIONED_REPOSITORY_PATH'],
			GENERIS_VERSIONED_REPOSITORY_LABEL,
			GENERIS_VERSIONED_REPOSITORY_COMMENT,
			'http://www.tao.lu/Ontologies/generis.rdf#DefaultRepository'
		);

		// Checkout the repository
		if (!is_null($repository)){
			//bypass the repository object because of loaded constants
			if(!self::testAuthentication($repository, $constants['GENERIS_VERSIONED_REPOSITORY_LOGIN'], $constants['GENERIS_VERSIONED_REPOSITORY_PASSWORD'])){
				//self::out("Unable to reach the remote versioning repository ".$constants['GENERIS_VERSIONED_REPOSITORY_URL']." ".$constants['GENERIS_VERSIONED_REPOSITORY_LOGIN'].":".$constants['GENERIS_VERSIONED_REPOSITORY_PASSWORD'], array('color' => 'light_red'));
				//self::out("Please check your configuration");
				throw new Exception("Unable to reach the remote versioning repository ".$constants['GENERIS_VERSIONED_REPOSITORY_URL']." ".$constants['GENERIS_VERSIONED_REPOSITORY_LOGIN'].":".$constants['GENERIS_VERSIONED_REPOSITORY_PASSWORD'].". Please check your configuration");
				return;
			} else {
				if(core_kernel_versioning_RepositoryProxy::singleton()->checkout($repository, $constants['GENERIS_VERSIONED_REPOSITORY_URL'], $constants['GENERIS_VERSIONED_REPOSITORY_PATH'])){
					//self::out("The remote versioning repository ".$constants['GENERIS_VERSIONED_REPOSITORY_URL']." is bound to TAO", array('color' => 'light_blue'));
					//self::out("local directory : ".$constants['GENERIS_VERSIONED_REPOSITORY_PATH']);
					common_Logger::i("The remote versioning repository ".$constants['GENERIS_VERSIONED_REPOSITORY_URL']." is bound to TAO. local directory : ".$constants['GENERIS_VERSIONED_REPOSITORY_PATH']);
				} else {
					//self::out('Unable to checkout the remote repository '.$constants['GENERIS_VERSIONED_REPOSITORY_URL'], array('color' => 'red'));
					throw new Exception('Unable to checkout the remote repository '.$constants['GENERIS_VERSIONED_REPOSITORY_URL']);
					return;
				}
			}
		} else {
			common_Logger::w('Repository is null');
		}
        // section 127-0-1-1-6b9a2186:1383319f00e:-8000:0000000000003B41 end
    }

    /**
     * Test the authentication to the repository
     *
     * @access public
     * @author Jehan Bihin
     * @param  string repository
     * @param  string login
     * @param  string password
     * @return boolean
     * @since 2.3
     */
    public static function testAuthentication($repository, $login, $password)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-6b9a2186:1383319f00e:-8000:0000000000003B49 begin
				$returnValue = core_kernel_versioning_RepositoryProxy::singleton()->authenticate($repository, $login, $password);
        // section 127-0-1-1-6b9a2186:1383319f00e:-8000:0000000000003B49 end

        return (bool) $returnValue;
    }

} /* end of class tao_helpers_Versionning */

?>