<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *
 */

/**
 * 'TAO_VERSION' 'TAO_VERSION_NAME' 'PRODUCT_NAME' 'TAO_RELEASE_STATUS' 'TAO_DEFAULT_ENCODING'
 * were moved to manifest
 */

/**
 * @deprecated
 */

$todefine = array(
	'TAO_OBJECT_CLASS' 					=> 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject',
	'TAO_GROUP_CLASS' 					=> 'http://www.tao.lu/Ontologies/TAOGroup.rdf#Group',
	'TAO_ITEM_CLASS' 					=> 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item',
//	not used
	'TAO_RESULT_CLASS' 					=> 'http://www.tao.lu/Ontologies/TAOResult.rdf#Result',
	'TAO_SUBJECT_CLASS' 				=> 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Subject',
	'TAO_TEST_CLASS' 					=> 'http://www.tao.lu/Ontologies/TAOTest.rdf#Test',
	'TAO_DELIVERY_CLASS' 				=> 'http://www.tao.lu/Ontologies/TAODelivery.rdf#Delivery',
	'GENERIS_RESOURCE'					=> 'http://www.tao.lu/Ontologies/generis.rdf#generis_Ressource',
	'INSTANCE_BOOLEAN_TRUE'				=> 'http://www.tao.lu/Ontologies/generis.rdf#True',
	'INSTANCE_BOOLEAN_FALSE'			=> 'http://www.tao.lu/Ontologies/generis.rdf#False',
	'TAO_LIST_CLASS'					=> 'http://www.tao.lu/Ontologies/TAO.rdf#List',
	'TAO_LIST_LEVEL_PROP'				=> 'http://www.tao.lu/Ontologies/TAO.rdf#level',
	'TAO_GUIORDER_PROP'					=> 'http://www.tao.lu/Ontologies/TAO.rdf#TAOGUIOrder',
	'CLASS_LANGUAGES'					=> 'http://www.tao.lu/Ontologies/TAO.rdf#Languages',
	'INSTANCE_ROLE_GLOBALMANAGER'		=> 'http://www.tao.lu/Ontologies/TAO.rdf#GlobalManagerRole',
//	not used
	'INSTANCE_ROLE_TAOMANAGER'			=> 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole',
	'INSTANCE_ROLE_SYSADMIN'			=> 'http://www.tao.lu/Ontologies/TAO.rdf#SysAdminRole',
	'INSTANCE_ROLE_BACKOFFICE'			=> 'http://www.tao.lu/Ontologies/TAO.rdf#BackOfficeRole',
//	not used
	'INSTANCE_ROLE_FRONTOFFICE'			=> 'http://www.tao.lu/Ontologies/TAO.rdf#FrontOfficeRole',
//	not used
	'INSTANCE_ROLE_SERVICE'				=> 'http://www.tao.lu/Ontologies/TAO.rdf#ServiceRole',
	'INSTANCE_ROLE_DELIVERY'			=> 'http://www.tao.lu/Ontologies/TAO.rdf#DeliveryRole',
	'TAO_INSTALLATOR'  					=> 'http://www.tao.lu/Ontologies/TAO.rdf#installator',
//	not used
	'PROPERTY_WIDGET_CALENDAR'			=> 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#Calendar',
	'PROPERTY_WIDGET_TEXTBOX'			=> 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox',
	'PROPERTY_WIDGET_TREEBOX'			=> 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TreeBox',
	'PROPERTY_WIDGET_TEXTAREA'			=> 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextArea',
	'PROPERTY_WIDGET_HTMLAREA'			=> 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HTMLArea',
//	not used
	'PROPERTY_WIDGET_PASSWORD'			=> 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#Password',
	'PROPERTY_WIDGET_HIDDENBOX'			=> 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HiddenBox',
	'PROPERTY_WIDGET_RADIOBOX'			=> 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#RadioBox',
	'PROPERTY_WIDGET_COMBOBOX'			=> 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ComboBox',
	'PROPERTY_WIDGET_CHECKBOX'			=> 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#CheckBox',
	'PROPERTY_WIDGET_FILE'				=> 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#AsyncFile',
//	not used
	'PROPERTY_WIDGET_VERSIONEDFILE'		=> 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#VersionedFile',
	'PROPERTY_WIDGET_JSONOBJECT'		=> 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#JsonObject',
	'PROPERTY_TAO_PROPERTY'				=> 'http://www.tao.lu/Ontologies/TAO.rdf#TAOProperty',
	'PROPERTY_LANGUAGE_USAGES'			=> 'http://www.tao.lu/Ontologies/TAO.rdf#LanguageUsages',
	'PROPERTY_LANGUAGE_ORIENTATION'     => 'http://www.tao.lu/Ontologies/TAO.rdf#LanguageOrientation',
	'INSTANCE_ORIENTATION_LTR'          => 'http://www.tao.lu/Ontologies/TAO.rdf#OrientationLeftToRight',
	'INSTANCE_ORIENTATION_RTL'          => 'http://www.tao.lu/Ontologies/TAO.rdf#OrientationRightToLeft',
	'CLASS_LANGUAGES_USAGES'			=> 'http://www.tao.lu/Ontologies/TAO.rdf#LanguagesUsages',
	'INSTANCE_LANGUAGE_USAGE_GUI'		=> 'http://www.tao.lu/Ontologies/TAO.rdf#LanguageUsageGUI',
	'INSTANCE_LANGUAGE_USAGE_DATA'		=> 'http://www.tao.lu/Ontologies/TAO.rdf#LanguageUsageData',
	'CLASS_PROCESS_EXECUTIONS'			=> 'http://www.tao.lu/middleware/taoqual.rdf#i119010455660544',
	'CLASS_MANAGEMENTROLE'				=> 'http://www.tao.lu/Ontologies/TAO.rdf#ManagementRole',
//	not used
	'CLASS_WORKERROLE'					=> 'http://www.tao.lu/Ontologies/TAO.rdf#WorkerRole',
	'CLASS_TAO_USER'					=> 'http://www.tao.lu/Ontologies/TAO.rdf#User',
	'DEFAULT_USER_URI_SUFFIX'			=> '#superUser',

	'CLASS_OAUTH_CONSUMER' 				=> 'http://www.tao.lu/Ontologies/TAO.rdf#OauthConsumer',
	'PROPERTY_OAUTH_KEY'			    => 'http://www.tao.lu/Ontologies/TAO.rdf#OauthKey',
	'PROPERTY_OAUTH_SECRET'             => 'http://www.tao.lu/Ontologies/TAO.rdf#OauthSecret',
	'PROPERTY_OAUTH_CALLBACK'           => 'http://www.tao.lu/Ontologies/TAO.rdf#OauthCallbackUrl',

	'INDEX_PROPERTY'                    => 'http://www.tao.lu/Ontologies/TAO.rdf#PropertyIndex',
	'INDEX_PROPERTY_FUZZY_MATCHING'     => 'http://www.tao.lu/Ontologies/TAO.rdf#IndexFuzzyMatching',
	'INDEX_PROPERTY_IDENTIFIER'         => 'http://www.tao.lu/Ontologies/TAO.rdf#IndexIdentifier',
	'INDEX_PROPERTY_TOKENIZER'          => 'http://www.tao.lu/Ontologies/TAO.rdf#IndexTokenizer',
	'INDEX_PROPERTY_DEFAULT_SEARCH'     => 'http://www.tao.lu/Ontologies/TAO.rdf#IndexDefaultSearch',

	// @todo properly migrate service and service calls to tao
	'CLASS_CALLOFSERVICES'							=> 'http://www.tao.lu/middleware/wfEngine.rdf#ClassCallOfServices',
	'PROPERTY_CALLOFSERVICES_SERVICEDEFINITION'		=> 'http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesServiceDefinition',
	'PROPERTY_CALLOFSERVICES_ACTUALPARAMETEROUT'	=> 'http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesActualParameterOut',
	'PROPERTY_CALLOFSERVICES_ACTUALPARAMETERIN'		=> 'http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesActualParameterin',
//	not used
	'PROPERTY_CALLOFSERVICES_TOP'					=> 'http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesTop',
//	not used
	'PROPERTY_CALLOFSERVICES_LEFT'					=> 'http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesLeft',
	'PROPERTY_CALLOFSERVICES_WIDTH'					=> 'http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesWidth',
	'PROPERTY_CALLOFSERVICES_HEIGHT'				=> 'http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesHeight',

	'CLASS_ACTUALPARAMETER'							=> 'http://www.tao.lu/middleware/wfEngine.rdf#ClassActualParameters',
	'PROPERTY_ACTUALPARAMETER_PROCESSVARIABLE'		=> 'http://www.tao.lu/middleware/wfEngine.rdf#PropertyActualParametersProcessVariable',
	'PROPERTY_ACTUALPARAMETER_CONSTANTVALUE'		=> 'http://www.tao.lu/middleware/wfEngine.rdf#PropertyActualParametersConstantValue',
	'PROPERTY_ACTUALPARAMETER_FORMALPARAMETER'		=> 'http://www.tao.lu/middleware/wfEngine.rdf#PropertyActualParametersFormalParameter',
//	not used
	'CLASS_SERVICESDEFINITION'						=> 'http://www.tao.lu/middleware/wfEngine.rdf#ClassServiceDefinitions',
//	not used
	'PROPERTY_SERVICESDEFINITION_FORMALPARAMOUT'	=> 'http://www.tao.lu/middleware/wfEngine.rdf#PropertyServiceDefinitionsFormalParameterOut',
//	not used
	'PROPERTY_SERVICESDEFINITION_FORMALPARAMIN' 	=> 'http://www.tao.lu/middleware/wfEngine.rdf#PropertyServiceDefinitionsFormalParameterIn',
//	not used
	'CLASS_SUPPORTSERVICES'							=> 'http://www.tao.lu/middleware/wfEngine.rdf#ClassSupportServices',
	'PROPERTY_SUPPORTSERVICES_URL'					=> 'http://www.tao.lu/middleware/wfEngine.rdf#PropertySupportServicesUrl',
//	not used
	'CLASS_WEBSERVICES'								=> 'http://www.tao.lu/middleware/wfEngine.rdf#ClassWebServices',
//	not used
	'CLASS_FORMALPARAMETER'							=> 'http://www.tao.lu/middleware/wfEngine.rdf#ClassFormalParameters',
//	not used
	'PROPERTY_FORMALPARAMETER_DEFAULTCONSTANTVALUE' => 'http://www.tao.lu/middleware/wfEngine.rdf#PropertyFormalParametersDefaultConstantValue',
//	not used
	'PROPERTY_FORMALPARAMETER_DEFAULTPROCESSVARIABLE'=>'http://www.tao.lu/middleware/wfEngine.rdf#PropertyFormalParametersDefaultProcessVariable',
	'PROPERTY_FORMALPARAMETER_NAME'					=> 'http://www.tao.lu/middleware/wfEngine.rdf#PropertyFormalParametersName',
	'PROPERTY_LOCK'                                 => 'http://www.tao.lu/Ontologies/TAO.rdf#Lock',

	'INSTANCE_ROLE_BASEUSER'                        => 'http://www.tao.lu/Ontologies/TAO.rdf#BaseUserRole',

	'PROPERTY_USER_FIRSTTIME'                       => 'http://www.tao.lu/Ontologies/TAO.rdf#FirstTimeInTao',
	'PROPERTY_USER_LASTEXTENSION'                   => 'http://www.tao.lu/Ontologies/TAO.rdf#LastExtensionUsed',

	// models
	'PROPERTY_ABSTRACTMODEL_STATUS'                 => 'http://www.tao.lu/Ontologies/TAO.rdf#AbstractModelStatus'
);

