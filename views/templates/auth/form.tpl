<?php
$events = get_data('events');
$authType = get_data('authType');
$allowedTypes = get_data('allowedTypes');
?>

<div class="wh-auth-selector-component">
    <div class="wh-auth-type-container">
        <label for="taoWebHookAuthType" class="form_desc"><?=__('Auth type')?></label>
        <select name="<?= \tao_helpers_Uri::encode(\oat\tao\model\webhooks\WebHookClassService::PROPERTY_AUTH_TYPE)?>" id="taoWebHookAuthType" class="wh-auth-type-selector">
            <?php foreach($allowedTypes as $type) :?>
                <option value="<?= $type->getAuthClass()->getUri() ?>"
                        <?php if ($type->getAuthClass()->getUri() == $authType->getAuthClass()->getUri()) :?>selected="selected"<?php endif; ?>
                ><?=$type->getAuthClass()->getLabel()?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="wh-authenticator-settings">
        <?php foreach ($allowedTypes as $allowedType) :?>
            <div data-auth-method="<?= $allowedType->getAuthClass()->getUri(); ?>" class="wh-auth-form-part<?php if ($allowedType->getAuthClass()->getUri() != $authType->getAuthClass()->getUri()) :?> hidden<?php endif; ?>">
            <?=$allowedType->getTemplate()?>
            </div>
        <?php endforeach; ?>
    </div>
    <hr/>
    <div class="bool-list">
        <label class="form_desc">Events</label>
        <div class="form_radlst form_checklst plain">
            <?php foreach($events as $eventClass=>$conf) :?>
                <div class="grid-row">
                    <div class="col-1"><input id="<?=$eventClass ?>" name="events[<?=$eventClass?>]" type="checkbox" <?=$conf['set']?'checked':''?>/></div>
                    <div class="col-10"><label class="elt_desc" for="<?=$eventClass?>" title="<?=$eventClass?>"><?=$conf['label']?></label></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
