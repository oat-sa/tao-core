<?php
use oat\tao\helpers\Template;

Template::inc('form_context.tpl', 'tao')
?>
<div class="main-container" data-tpl="tao/form.tpl">
    <h2><?=get_data('formTitle')?></h2>
    <div class="form-content">
        <?if(has_data('errorMessage')):?>
            <fieldset class='ui-state-error'>
                <legend><strong><?=__('Error')?></strong></legend>
                <?=get_data('errorMessage')?>
            </fieldset>
        <?endif?>
        <?=get_data('myForm')?>
    </div>
</div>
<div class="data-container-wrapper"></div>