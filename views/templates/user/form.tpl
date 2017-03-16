<?php
use oat\tao\helpers\Template;

$class = new core_kernel_classes_Class(CLASS_TAO_USER);
$userService = tao_models_classes_UserService::singleton();


// Label
$userLabel = $userService->createUniqueLabel($class);

$defaultProps = tao_helpers_form_GenerisFormFactory::getDefaultProperties();
//$userForm = new tao_actions_form_Users($class);
//$classProps = tao_helpers_form_GenerisFormFactory::getClassProperties($class, $userForm->getTopClazz());
$classProps = $class->getProperties(true);

//print('<pre>'.print_r($classProps, true).'</pre>');

foreach ($classProps as $prop) {
    print('<pre>'.print_r($prop->getWidget(), true).'</pre>');
}
?>

<div class="main-container flex-container-main-form">
    <h2><?=get_data('formTitle')?></h2>
    <div class="form-container">
        <?=get_data('myForm')?>
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
        var f = form()
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
        // <div>
        //     <label
        //         class="form_desc"
        //         for="http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userFirstName">
        //         First Name
        //     </label>
        //     <input
        //         type="text"
        //         name="http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userFirstName"
        //         id="http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userFirstName"
        //         value="">
        // </div>
        .addField({
            object : {
                input : {
                    name : '',
                    rdfs : ''
                },
                label : 'First Name',
                type : 'text'
            }
        })
        ;
    });
</script>



<?php Template::inc('footer.tpl'); ?>
