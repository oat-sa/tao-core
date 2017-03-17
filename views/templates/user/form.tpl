<?php
use oat\tao\helpers\Template;

$class = new core_kernel_classes_Class(CLASS_TAO_USER);
$userService = tao_models_classes_UserService::singleton();

// Label
$userLabel = $userService->createUniqueLabel($class);

?>

<div class="main-container flex-container-main-form">
    <h2><?=get_data('formTitle')?></h2>
    <div class="form-container">
        <!--<?=get_data('myForm')?>-->
    </div>
</div>

<script>
    requirejs.config({
        config : {
            'tao/controller/users/add' : {
                loginId : <?=json_encode(get_data('loginUri'))?>,
                exit    : <?=json_encode(get_data('exit'))?>
            }
        } 
    });		

    require([
        'ui/form/form'
    ], function(form) {
        'use strict';

        // TODO: set form name to 'user_form'
        var f = form({
            method : 'post',
            name : 'user_form',
            action : '/tao/users/add'
        })

        // TODO use id
        .attachTo('.form-container')

        // Label
        .addField({
            object : {
                input : {
                    // TODO: what to do with this? and set it as a uri?
                    name : 'http_2_www_0_w3_0_org_1_2000_1_01_1_rdf-schema_3_label',
                    rdfs : 'http://www.w3.org/2000/01/rdf-schema#label',
                    value : '<?= $userLabel ?>',
                },
                label : 'Label',
                required : true,
                type : 'text'
            }
        })

        // First Name
        .addField({
            object : {
                input : {
                    name : 'http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userFirstName'
                },
                label : 'First Name',
                type : 'text'
            }
        })

        // Last Name
        .addField({
            object : {
                input : {
                    name : 'http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userLastName'
                },
                label : 'Last Name',
                type : 'text'
            }
        })

        // Email
        .addField({
            object : {
                input : {
                    name : 'http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userMail'
                },
                label : 'Email',
                type : 'text'
            }
        })

        // Data Language
        .addField({
            object : {
                input : {
                    name : 'http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userDefLg',
                    options : [
                        { value : ' ', label : ' ' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langda-DK', label : 'Danish' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langde-DE', label : 'German' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langel-GR', label : 'Greek' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langen-US', label : 'English' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langes-ES', label : 'Spanish' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langfr-FR', label : 'French' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langis-IS', label : 'Icelandic' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langit-IT', label : 'Italian' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langja-JP', label : 'Japanese' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langnl-NL', label : 'Dutch' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langpt-PT', label : 'Portuguese' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langsv-SE', label : 'Swedish' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Languk-UA', label : 'Ukrainian' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langzh-CN', label : 'Simplified Chinese from China' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langzh-TW', label : 'Traditional Chinese from Taiwan' }
                    ]
                },
                label : 'Data Language',
                required : true,
                type : 'select'
            }
        })

        // Interface Language
        .addField({
            object : {
                input : {
                    name : 'http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userUILg',
                    options : [
                        { value : ' ', label : ' ' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langda-DK', label : 'Danish' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langde-DE', label : 'German' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langel-GR', label : 'Greek' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langen-US', label : 'English' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langes-ES', label : 'Spanish' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langfr-FR', label : 'French' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langis-IS', label : 'Icelandic' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langit-IT', label : 'Italian' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langja-JP', label : 'Japanese' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langnl-NL', label : 'Dutch' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langpt-PT', label : 'Portuguese' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langsv-SE', label : 'Swedish' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Languk-UA', label : 'Ukrainian' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langzh-CN', label : 'Simplified Chinese from China' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langzh-TW', label : 'Traditional Chinese from Taiwan' }
                    ]
                },
                label : 'Interface Language',
                required : true,
                type : 'select'
            }
        })

        // Login
        .addField({
            object : {
                input : {
                    name : 'http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_login'
                },
                label : 'Login',
                required : true,
                type : 'text'
            }
        })

        // Roles
        .addField({
            object : {
                input : {
                    name : 'http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userRoles',
                    options : [
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_GlobalManagerRole',  name : 'http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userRoles_0', label : 'Global Manager' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAOItem_0_rdf_3_ItemAuthor',     name : 'http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userRoles_1', label : 'Item Author' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_LockManagerRole',    name : 'http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userRoles_2', label : 'Lock Manager' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAOProctor_0_rdf_3_ProctorRole', name : 'http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userRoles_3', label : 'Proctor' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_SysAdminRole',       name : 'http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userRoles_4', label : 'System Administrator' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_TaskQueueManager',   name : 'http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userRoles_5', label : 'Task Queue Manager' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAOItem_0_rdf_3_TestAuthor',     name : 'http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userRoles_6', label : 'Test Author' },
                        { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_DeliveryRole',       name : 'http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userRoles_7', label : 'Test Taker' }
                    ]
                },
                label : 'Roles',
                type : 'checkbox_list'
            }
        })

        // Password
        .addField({
            object : {
                input : {
                    name : 'password1'
                },
                label : 'Password',
                required : true,
                type : 'password'
            }
        })

        // Repeat Password
        .addField({
            object : {
                input : {
                    name : 'password2'
                },
                label : 'Repeat Password',
                required : true,
                type : 'password'
            }
        })
        ;
    });
</script>



<?php Template::inc('footer.tpl'); ?>
