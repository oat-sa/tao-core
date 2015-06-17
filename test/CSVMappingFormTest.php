<?php

use oat\tao\test\TaoPhpUnitTestRunner;

include_once dirname(__FILE__) . '/../includes/raw_start.php';

class CSVMappingFormTestCase extends TaoPhpUnitTestRunner
{
    public function testMapping()
    {

        $csv_column = ['login', 'password', 'title', 'lastname', 'firstname', 'gender', 'email', 'picture', 'address'];

        $properties = [
            'http_2_www_0_w3_0_org_1_2000_1_01_1_rdf-schema_3_label' => 'Label',
            'http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userFirstName' => 'First Name',
            'http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userLastName' => 'Last Name',
            'http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_login' => 'Login',
            'http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_password' => 'Password',
            'http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userMail' => 'Mail',
            'http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userUILg' => 'Interface Language',
            'http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_passwordRecoveryToken' => 'Password recovery token',
            'http_2_www_0_tao_0_lu_1_Ontologies_1_TAOGroup_0_rdf_3_member' => 'Member'
        ];

        $data = array();
        $options = array(
            'class_properties' => $properties,
            'ranged_properties' => array(),
            'csv_column' => $csv_column,
            tao_helpers_data_CsvFile::FIRST_ROW_COLUMN_NAMES => 'first_row_column_names',
        );

        $formContainer = new tao_models_classes_import_CSVMappingForm($data, $options);
        $form = $formContainer->getForm();

        $this->assertEquals('csv_select', $form->getElement('http_2_www_0_w3_0_org_1_2000_1_01_1_rdf-schema_3_label')->getEvaluatedValue());
        $this->assertEquals('4_O', $form->getElement('http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userFirstName')->getEvaluatedValue());
        $this->assertEquals('3_O', $form->getElement('http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userLastName')->getEvaluatedValue());
        $this->assertEquals('0_O', $form->getElement('http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_login')->getEvaluatedValue());
        $this->assertEquals('1_O', $form->getElement('http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_password')->getEvaluatedValue());
        $this->assertEquals('6_O', $form->getElement('http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userMail')->getEvaluatedValue());
        $this->assertEquals('csv_select', $form->getElement('http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userUILg')->getEvaluatedValue());
        $this->assertEquals('csv_select', $form->getElement('http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_passwordRecoveryToken')->getEvaluatedValue());
        $this->assertEquals('csv_select', $form->getElement('http_2_www_0_tao_0_lu_1_Ontologies_1_TAOGroup_0_rdf_3_member')->getEvaluatedValue());

    }
}
