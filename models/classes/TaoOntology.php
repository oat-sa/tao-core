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
    const CLASS_URI_OBJECT = 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject';
    const CLASS_URI_GROUP = 'http://www.tao.lu/Ontologies/TAOGroup.rdf#Group';
    const CLASS_URI_ITEM = 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item';
    const CLASS_URI_RESULT = 'http://www.tao.lu/Ontologies/TAOResult.rdf#Result';
    const CLASS_URI_SUBJECT = 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Subject';
    const CLASS_URI_TEST = 'http://www.tao.lu/Ontologies/TAOTest.rdf#Test';
    const CLASS_URI_DELIVERY = 'http://www.tao.lu/Ontologies/TAODelivery.rdf#Delivery';
    const CLASS_URI_LIST = 'http://www.tao.lu/Ontologies/TAO.rdf#List';
    const CLASS_URI_PROCESS_EXECUTIONS = 'http://www.tao.lu/middleware/taoqual.rdf#i119010455660544';
    const CLASS_URI_MANAGEMENT_ROLE = 'http://www.tao.lu/Ontologies/TAO.rdf#ManagementRole';
    const CLASS_URI_WORKER_ROLE = 'http://www.tao.lu/Ontologies/TAO.rdf#WorkerRole';
    const CLASS_URI_TAO_USER = 'http://www.tao.lu/Ontologies/TAO.rdf#User';
    const PROPERTY_UPDATED_AT = 'http://www.tao.lu/Ontologies/TAO.rdf#UpdatedAt';
    const PROPERTY_LIST_LEVEL = 'http://www.tao.lu/Ontologies/TAO.rdf#level';
    const PROPERTY_INSTALLATOR = 'http://www.tao.lu/Ontologies/TAO.rdf#installator';
    const PROPERTY_USER_FIRST_TIME = 'http://www.tao.lu/Ontologies/TAO.rdf#FirstTimeInTao';
    const PROPERTY_USER_LAST_EXTENSION = 'http://www.tao.lu/Ontologies/TAO.rdf#LastExtensionUsed';
    const PROPERTY_ABSTRACT_MODEL_STATUS = 'http://www.tao.lu/Ontologies/TAO.rdf#AbstractModelStatus';
    const PROPERTY_LOCK = 'http://www.tao.lu/Ontologies/TAO.rdf#Lock';
    const PROPERTY_GUI_ORDER = 'http://www.tao.lu/Ontologies/TAO.rdf#TAOGUIOrder';
    const DEFAULT_USER_URI_SUFFIX = '#superUser';

    /** @deprecated use TaoRoles::TAO_MANAGER */
    const PROPERTY_INSTANCE_ROLE_TAO_MANAGER = 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole';
    /** @deprecated use TaoOntology::CLASS_URI_OBJECT */
    const OBJECT_CLASS_URI = 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject';
    /** @deprecated use TaoOntology::CLASS_URI_GROUP */
    const GROUP_CLASS_URI = 'http://www.tao.lu/Ontologies/TAOGroup.rdf#Group';
    /** @deprecated use TaoOntology::CLASS_URI_ITEM */
    const ITEM_CLASS_URI = 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item';
    /** @deprecated use TaoOntology::CLASS_URI_RESULT */
    const RESULT_CLASS_URI = 'http://www.tao.lu/Ontologies/TAOResult.rdf#Result';
    /** @deprecated use TaoOntology::CLASS_URI_SUBJECT */
    const SUBJECT_CLASS_URI = 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Subject';
    /** @deprecated use TaoOntology::CLASS_URI_TEST */
    const TEST_CLASS_URI = 'http://www.tao.lu/Ontologies/TAOTest.rdf#Test';
    /** @deprecated use TaoOntology::CLASS_URI_DELIVERY */
    const DELIVERY_CLASS_URI = 'http://www.tao.lu/Ontologies/TAODelivery.rdf#Delivery';
    /** @deprecated use TaoOntology::CLASS_URI_LIST */
    const LIST_CLASS_URI = 'http://www.tao.lu/Ontologies/TAO.rdf#List';
    /** @deprecated use TaoOntology::PROPERTY_LIST_LEVEL */
    const LIST_LEVEL_PROP = 'http://www.tao.lu/Ontologies/TAO.rdf#level';
    /** @deprecated use TaoOntology::PROPERTY_GUI_ORDER */
    const GUI_ORDER_PROP = 'http://www.tao.lu/Ontologies/TAO.rdf#TAOGUIOrder';
    /** @deprecated use TaoRoles::GLOBAL_MANAGER */
    const PROPERTY_INSTANCE_ROLE_GLOBALMANAGER = 'http://www.tao.lu/Ontologies/TAO.rdf#GlobalManagerRole';
    /** @deprecated use TaoRoles::SYSTEM_ADMINISTRATOR */
    const PROPERTY_INSTANCE_ROLE_SYSADMIN = 'http://www.tao.lu/Ontologies/TAO.rdf#SysAdminRole';
    /** @deprecated use TaoRoles::BACK_OFFICE */
    const PROPERTY_INSTANCE_ROLE_BACKOFFICE = 'http://www.tao.lu/Ontologies/TAO.rdf#BackOfficeRole';
    /** @deprecated use TaoRoles::DELIVERY */
    const PROPERTY_INSTANCE_ROLE_DELIVERY = 'http://www.tao.lu/Ontologies/TAO.rdf#DeliveryRole';
    /** @deprecated use DataStore::CLASS_URI_OAUTH_CONSUMER */
    const CLASS_URI_OAUTH_CONSUMER = 'http://www.tao.lu/Ontologies/TAO.rdf#OauthConsumer';
    /** @deprecated use DataStore::PROPERTY_OAUTH_KEY */
    const PROPERTY_OAUTH_KEY = 'http://www.tao.lu/Ontologies/TAO.rdf#OauthKey';
    /** @deprecated use DataStore::PROPERTY_OAUTH_SECRET */
    const PROPERTY_OAUTH_SECRET = 'http://www.tao.lu/Ontologies/TAO.rdf#OauthSecret';
    /** @deprecated use DataStore::PROPERTY_OAUTH_CALLBACK */
    const PROPERTY_OAUTH_CALLBACK = 'http://www.tao.lu/Ontologies/TAO.rdf#OauthCallbackUrl';
    /** @deprecated use Index::PROPERTY_INDEX */
    const INDEX_PROPERTY = 'http://www.tao.lu/Ontologies/TAO.rdf#PropertyIndex';
    /** @deprecated use Index::PROPERTY_INDEX_FUZZY_MATCHING */
    const INDEX_PROPERTY_FUZZY_MATCHING = 'http://www.tao.lu/Ontologies/TAO.rdf#IndexFuzzyMatching';
    /** @deprecated use Index::PROPERTY_INDEX_IDENTIFIER */
    const INDEX_PROPERTY_IDENTIFIER = 'http://www.tao.lu/Ontologies/TAO.rdf#IndexIdentifier';
    /** @deprecated use Index::PROPERTY_INDEX_TOKENIZER */
    const INDEX_PROPERTY_TOKENIZER = 'http://www.tao.lu/Ontologies/TAO.rdf#IndexTokenizer';
    /** @deprecated use TaoRoles::BASE_USER */
    const PROPERTY_INSTANCE_ROLE_BASE_USER = 'http://www.tao.lu/Ontologies/TAO.rdf#BaseUserRole';
    /** @deprecated use Index::PROPERTY_DEFAULT_SEARCH */
    const INDEX_PROPERTY_DEFAULT_SEARCH = 'http://www.tao.lu/Ontologies/TAO.rdf#IndexDefaultSearch';
    /** @deprecated use tao_models_classes_LanguageService::CLASS_URI_LANGUAGES */
    const CLASS_URI_LANGUAGES = 'http://www.tao.lu/Ontologies/TAO.rdf#Languages';
    /** @deprecated use tao_models_classes_LanguageService::PROPERTY_LANGUAGE_USAGES */
    const PROPERTY_LANGUAGE_USAGES = 'http://www.tao.lu/Ontologies/TAO.rdf#LanguageUsages';
    /** @deprecated use tao_models_classes_LanguageService::PROPERTY_LANGUAGE_ORIENTATION */
    const PROPERTY_LANGUAGE_ORIENTATION = 'http://www.tao.lu/Ontologies/TAO.rdf#LanguageOrientation';
    /** @deprecated use tao_models_classes_LanguageService::INSTANCE_ORIENTATION_LTR */
    const PROPERTY_INSTANCE_ORIENTATION_LTR = 'http://www.tao.lu/Ontologies/TAO.rdf#OrientationLeftToRight';
    /** @deprecated use tao_models_classes_LanguageService::INSTANCE_ORIENTATION_RTL */
    const PROPERTY_INSTANCE_ORIENTATION_RTL = 'http://www.tao.lu/Ontologies/TAO.rdf#OrientationRightToLeft';
    /** @deprecated use tao_models_classes_LanguageService::CLASS_URI_LANGUAGES_USAGES */
    const CLASS_URI_LANGUAGES_USAGES = 'http://www.tao.lu/Ontologies/TAO.rdf#LanguagesUsages';
    /** @deprecated use tao_models_classes_LanguageService::INSTANCE_LANGUAGE_USAGE_GUI */
    const PROPERTY_INSTANCE_LANGUAGE_USAGE_GUI = 'http://www.tao.lu/Ontologies/TAO.rdf#LanguageUsageGUI';
    /** @deprecated use tao_models_classes_LanguageService::INSTANCE_LANGUAGE_USAGE_DATA */
    const PROPERTY_STANCE_LANGUAGE_USAGE_DATA = 'http://www.tao.lu/Ontologies/TAO.rdf#LanguageUsageData';
    /** @deprecated use tao_models_classes_LanguageService::CLASS_URI_LANGUAGES */
    const LANGUAGES_CLASS_URI = 'http://www.tao.lu/Ontologies/TAO.rdf#Languages';
}
