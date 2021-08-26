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
    public const CLASS_URI_OBJECT = 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject';
    public const CLASS_URI_GROUP = 'http://www.tao.lu/Ontologies/TAOGroup.rdf#Group';
    public const CLASS_URI_ITEM = 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item';
    public const CLASS_URI_RESULT = 'http://www.tao.lu/Ontologies/TAOResult.rdf#Result';
    public const CLASS_URI_SUBJECT = 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Subject';
    public const CLASS_URI_TEST = 'http://www.tao.lu/Ontologies/TAOTest.rdf#Test';
    public const CLASS_URI_DELIVERY = 'http://www.tao.lu/Ontologies/TAODelivery.rdf#Delivery';
    public const CLASS_URI_ASSEMBLED_DELIVERY = 'http://www.tao.lu/Ontologies/TAODelivery.rdf#AssembledDelivery';
    public const CLASS_URI_LIST = 'http://www.tao.lu/Ontologies/TAO.rdf#List';
    public const CLASS_URI_PROCESS_EXECUTIONS = 'http://www.tao.lu/middleware/taoqual.rdf#i119010455660544';
    public const CLASS_URI_MANAGEMENT_ROLE = 'http://www.tao.lu/Ontologies/TAO.rdf#ManagementRole';
    public const CLASS_URI_WORKER_ROLE = 'http://www.tao.lu/Ontologies/TAO.rdf#WorkerRole';
    public const CLASS_URI_TAO_USER = 'http://www.tao.lu/Ontologies/TAO.rdf#User';
    public const CLASS_URI_TREE = 'http://www.tao.lu/Ontologies/TAO.rdf#Tree';

    public const PROPERTY_UPDATED_AT = 'http://www.tao.lu/Ontologies/TAO.rdf#UpdatedAt';
    public const PROPERTY_LIST_LEVEL = 'http://www.tao.lu/Ontologies/TAO.rdf#level';
    public const PROPERTY_USER_FIRST_TIME = 'http://www.tao.lu/Ontologies/TAO.rdf#FirstTimeInTao';
    public const PROPERTY_USER_LAST_EXTENSION = 'http://www.tao.lu/Ontologies/TAO.rdf#LastExtensionUsed';

    public const PROPERTY_ABSTRACT_MODEL_STATUS = 'http://www.tao.lu/Ontologies/TAO.rdf#AbstractModelStatus';
    public const PROPERTY_LOCK = 'http://www.tao.lu/Ontologies/TAO.rdf#Lock';
    public const PROPERTY_GUI_ORDER = 'http://www.tao.lu/Ontologies/TAO.rdf#TAOGUIOrder';
    public const DEFAULT_USER_URI_SUFFIX = '#superUser';

    /** @deprecated use TaoRoles::TAO_MANAGER */
    public const PROPERTY_INSTANCE_ROLE_TAO_MANAGER = 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole';
    /** @deprecated use TaoOntology::CLASS_URI_OBJECT */
    public const OBJECT_CLASS_URI = 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject';
    /** @deprecated use TaoOntology::CLASS_URI_GROUP */
    public const GROUP_CLASS_URI = 'http://www.tao.lu/Ontologies/TAOGroup.rdf#Group';
    /** @deprecated use TaoOntology::CLASS_URI_ITEM */
    public const ITEM_CLASS_URI = 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item';
    /** @deprecated use TaoOntology::CLASS_URI_RESULT */
    public const RESULT_CLASS_URI = 'http://www.tao.lu/Ontologies/TAOResult.rdf#Result';
    /** @deprecated use TaoOntology::CLASS_URI_SUBJECT */
    public const SUBJECT_CLASS_URI = 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Subject';
    /** @deprecated use TaoOntology::CLASS_URI_TEST */
    public const TEST_CLASS_URI = 'http://www.tao.lu/Ontologies/TAOTest.rdf#Test';
    /** @deprecated use TaoOntology::CLASS_URI_DELIVERY */
    public const DELIVERY_CLASS_URI = 'http://www.tao.lu/Ontologies/TAODelivery.rdf#Delivery';
    /** @deprecated use TaoOntology::CLASS_URI_LIST */
    public const LIST_CLASS_URI = 'http://www.tao.lu/Ontologies/TAO.rdf#List';
    /** @deprecated use TaoOntology::PROPERTY_LIST_LEVEL */
    public const LIST_LEVEL_PROP = 'http://www.tao.lu/Ontologies/TAO.rdf#level';
    /** @deprecated use TaoOntology::PROPERTY_GUI_ORDER */
    public const GUI_ORDER_PROP = 'http://www.tao.lu/Ontologies/TAO.rdf#TAOGUIOrder';
    /** @deprecated use TaoRoles::GLOBAL_MANAGER */
    public const PROPERTY_INSTANCE_ROLE_GLOBALMANAGER = 'http://www.tao.lu/Ontologies/TAO.rdf#GlobalManagerRole';
    /** @deprecated use TaoRoles::SYSTEM_ADMINISTRATOR */
    public const PROPERTY_INSTANCE_ROLE_SYSADMIN = 'http://www.tao.lu/Ontologies/TAO.rdf#SysAdminRole';
    /** @deprecated use TaoRoles::BACK_OFFICE */
    public const PROPERTY_INSTANCE_ROLE_BACKOFFICE = 'http://www.tao.lu/Ontologies/TAO.rdf#BackOfficeRole';
    /** @deprecated use TaoRoles::DELIVERY */
    public const PROPERTY_INSTANCE_ROLE_DELIVERY = 'http://www.tao.lu/Ontologies/TAO.rdf#DeliveryRole';
    /** @deprecated use DataStore::CLASS_URI_OAUTH_CONSUMER */
    public const CLASS_URI_OAUTH_CONSUMER = 'http://www.tao.lu/Ontologies/TAO.rdf#OauthConsumer';
    /** @deprecated use DataStore::PROPERTY_OAUTH_KEY */
    public const PROPERTY_OAUTH_KEY = 'http://www.tao.lu/Ontologies/TAO.rdf#OauthKey';
    /** @deprecated use DataStore::PROPERTY_OAUTH_SECRET */
    public const PROPERTY_OAUTH_SECRET = 'http://www.tao.lu/Ontologies/TAO.rdf#OauthSecret';
    /** @deprecated use DataStore::PROPERTY_OAUTH_CALLBACK */
    public const PROPERTY_OAUTH_CALLBACK = 'http://www.tao.lu/Ontologies/TAO.rdf#OauthCallbackUrl';
    /** @deprecated use Index::PROPERTY_INDEX */
    public const INDEX_PROPERTY = 'http://www.tao.lu/Ontologies/TAO.rdf#PropertyIndex';
    /** @deprecated use Index::PROPERTY_INDEX_FUZZY_MATCHING */
    public const INDEX_PROPERTY_FUZZY_MATCHING = 'http://www.tao.lu/Ontologies/TAO.rdf#IndexFuzzyMatching';
    /** @deprecated use Index::PROPERTY_INDEX_IDENTIFIER */
    public const INDEX_PROPERTY_IDENTIFIER = 'http://www.tao.lu/Ontologies/TAO.rdf#IndexIdentifier';
    /** @deprecated use Index::PROPERTY_INDEX_TOKENIZER */
    public const INDEX_PROPERTY_TOKENIZER = 'http://www.tao.lu/Ontologies/TAO.rdf#IndexTokenizer';
    /** @deprecated use TaoRoles::BASE_USER */
    public const PROPERTY_INSTANCE_ROLE_BASE_USER = 'http://www.tao.lu/Ontologies/TAO.rdf#BaseUserRole';
    /** @deprecated use Index::PROPERTY_DEFAULT_SEARCH */
    public const INDEX_PROPERTY_DEFAULT_SEARCH = 'http://www.tao.lu/Ontologies/TAO.rdf#IndexDefaultSearch';
    /** @deprecated use tao_models_classes_LanguageService::CLASS_URI_LANGUAGES */
    public const CLASS_URI_LANGUAGES = 'http://www.tao.lu/Ontologies/TAO.rdf#Languages';
    /** @deprecated use tao_models_classes_LanguageService::PROPERTY_LANGUAGE_USAGES */
    public const PROPERTY_LANGUAGE_USAGES = 'http://www.tao.lu/Ontologies/TAO.rdf#LanguageUsages';
    /** @deprecated use tao_models_classes_LanguageService::PROPERTY_LANGUAGE_ORIENTATION */
    public const PROPERTY_LANGUAGE_ORIENTATION = 'http://www.tao.lu/Ontologies/TAO.rdf#LanguageOrientation';
    /** @deprecated use tao_models_classes_LanguageService::INSTANCE_ORIENTATION_LTR */
    public const PROPERTY_INSTANCE_ORIENTATION_LTR = 'http://www.tao.lu/Ontologies/TAO.rdf#OrientationLeftToRight';
    /** @deprecated use tao_models_classes_LanguageService::INSTANCE_ORIENTATION_RTL */
    public const PROPERTY_INSTANCE_ORIENTATION_RTL = 'http://www.tao.lu/Ontologies/TAO.rdf#OrientationRightToLeft';
    /** @deprecated use tao_models_classes_LanguageService::CLASS_URI_LANGUAGES_USAGES */
    public const CLASS_URI_LANGUAGES_USAGES = 'http://www.tao.lu/Ontologies/TAO.rdf#LanguagesUsages';
    /** @deprecated use tao_models_classes_LanguageService::INSTANCE_LANGUAGE_USAGE_GUI */
    public const PROPERTY_INSTANCE_LANGUAGE_USAGE_GUI = 'http://www.tao.lu/Ontologies/TAO.rdf#LanguageUsageGUI';
    /** @deprecated use tao_models_classes_LanguageService::INSTANCE_LANGUAGE_USAGE_DATA */
    public const PROPERTY_STANCE_LANGUAGE_USAGE_DATA = 'http://www.tao.lu/Ontologies/TAO.rdf#LanguageUsageData';
    /** @deprecated use tao_models_classes_LanguageService::CLASS_URI_LANGUAGES */
    public const LANGUAGES_CLASS_URI = 'http://www.tao.lu/Ontologies/TAO.rdf#Languages';
}
