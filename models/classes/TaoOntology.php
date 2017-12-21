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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model;

interface TaoOntology
{
	const OBJECT_CLASS_URI = 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject';
	const GROUP_CLASS_URI = 'http://www.tao.lu/Ontologies/TAOGroup.rdf#Group';
	const ITEM_CLASS_URI = 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item';
	const RESULT_CLASS_URI = 'http://www.tao.lu/Ontologies/TAOResult.rdf#Result';
	const SUBJECT_CLASS_URI = 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Subject';
	const TEST_CLASS_URI = 'http://www.tao.lu/Ontologies/TAOTest.rdf#Test';
	const DELIVERY_CLASS_URI = 'http://www.tao.lu/Ontologies/TAODelivery.rdf#Delivery';
	const LIST_CLASS_URI = 'http://www.tao.lu/Ontologies/TAO.rdf#List';
	const PROPERTY_UPDATED_AT = 'http://www.tao.lu/Ontologies/TAO.rdf#UpdatedAt';
	const LIST_LEVEL_PROP = 'http://www.tao.lu/Ontologies/TAO.rdf#level';
	const GUI_ORDER_PROP = 'http://www.tao.lu/Ontologies/TAO.rdf#TAOGUIOrder';
	const LANGUAGES_CLASS_URI = 'http://www.tao.lu/Ontologies/TAO.rdf#Languages';
	const PROPERTY_INSTANCE_ROLE_GLOBALMANAGER = 'http://www.tao.lu/Ontologies/TAO.rdf#GlobalManagerRole';
	const PROPERTY_INSTANCE_ROLE_TAO_MANAGER = 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole';
	const PROPERTY_INSTANCE_ROLE_SYSADMIN = 'http://www.tao.lu/Ontologies/TAO.rdf#SysAdminRole';
	const PROPERTY_INSTANCE_ROLE_BACKOFFICE = 'http://www.tao.lu/Ontologies/TAO.rdf#BackOfficeRole';
	const PROPERTY_INSTANCE_ROLE_FRONTOFFICE = 'http://www.tao.lu/Ontologies/TAO.rdf#FrontOfficeRole';
	const PROPERTY_INSTANCE_ROLE_SERVICE = 'http://www.tao.lu/Ontologies/TAO.rdf#ServiceRole';
	const PROPERTY_INSTANCE_ROLE_DELIVERY = 'http://www.tao.lu/Ontologies/TAO.rdf#DeliveryRole';
	const PROPERTY_INSTALLATOR = 'http://www.tao.lu/Ontologies/TAO.rdf#installator';
	const PROPERTY_TAO_PROPERTY = 'http://www.tao.lu/Ontologies/TAO.rdf#TAOProperty';
	const PROPERTY_LANGUAGE_USAGES = 'http://www.tao.lu/Ontologies/TAO.rdf#LanguageUsages';
	const PROPERTY_LANGUAGE_ORIENTATION = 'http://www.tao.lu/Ontologies/TAO.rdf#LanguageOrientation';
	const PROPERTY_INSTANCE_ORIENTATION_LTR = 'http://www.tao.lu/Ontologies/TAO.rdf#OrientationLeftToRight';
	const PROPERTY_INSTANCE_ORIENTATION_RTL = 'http://www.tao.lu/Ontologies/TAO.rdf#OrientationRightToLeft';
	const CLASS_URI_LANGUAGES_USAGES = 'http://www.tao.lu/Ontologies/TAO.rdf#LanguagesUsages';
	const PROPERTY_INSTANCE_LANGUAGE_USAGE_GUI = 'http://www.tao.lu/Ontologies/TAO.rdf#LanguageUsageGUI';
	const PROPERTY_STANCE_LANGUAGE_USAGE_DATA = 'http://www.tao.lu/Ontologies/TAO.rdf#LanguageUsageData';
	const CLASS_URI_PROCESS_EXECUTIONS = 'http://www.tao.lu/middleware/taoqual.rdf#i119010455660544';
	const CLASS_URI_MANAGEMENT_ROLE = 'http://www.tao.lu/Ontologies/TAO.rdf#ManagementRole';
	const CLASS_URI_WORKER_ROLE = 'http://www.tao.lu/Ontologies/TAO.rdf#WorkerRole';
	const CLASS_URI_TAO_USER = 'http://www.tao.lu/Ontologies/TAO.rdf#User';
	const DEFAULT_USER_URI_SUFFIX = '#superUser';
	const CLASS_URI_OAUTH_CONSUMER = 'http://www.tao.lu/Ontologies/TAO.rdf#OauthConsumer';
	const PROPERTY_OAUTH_KEY = 'http://www.tao.lu/Ontologies/TAO.rdf#OauthKey';
	const PROPERTY_OAUTH_SECRET = 'http://www.tao.lu/Ontologies/TAO.rdf#OauthSecret';
	const PROPERTY_OAUTH_CALLBACK = 'http://www.tao.lu/Ontologies/TAO.rdf#OauthCallbackUrl';
	const INDEX_PROPERTY = 'http://www.tao.lu/Ontologies/TAO.rdf#PropertyIndex';
	const INDEX_PROPERTY_FUZZY_MATCHING = 'http://www.tao.lu/Ontologies/TAO.rdf#IndexFuzzyMatching';
	const INDEX_PROPERTY_IDENTIFIER = 'http://www.tao.lu/Ontologies/TAO.rdf#IndexIdentifier';
	const INDEX_PROPERTY_TOKENIZER = 'http://www.tao.lu/Ontologies/TAO.rdf#IndexTokenizer';
	const INDEX_PROPERTY_DEFAULT_SEARCH = 'http://www.tao.lu/Ontologies/TAO.rdf#IndexDefaultSearch';
	const PROPERTY_LOCK = 'http://www.tao.lu/Ontologies/TAO.rdf#Lock';
	const PROPERTY_INSTANCE_ROLE_BASE_USER = 'http://www.tao.lu/Ontologies/TAO.rdf#BaseUserRole';
	const PROPERTY_USER_FIRST_TIME = 'http://www.tao.lu/Ontologies/TAO.rdf#FirstTimeInTao';
	const PROPERTY_USER_LAST_EXTENSION = 'http://www.tao.lu/Ontologies/TAO.rdf#LastExtensionUsed';
	const PROPERTY_ABSTRACT_MODEL_STATUS = 'http://www.tao.lu/Ontologies/TAO.rdf#AbstractModelStatus';
}