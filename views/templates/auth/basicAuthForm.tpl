<div class="basic-auth-form">
    <div class="login-container">
        <label for="login" class="form_desc"><?=__('Login')?></label>
        <input type="text" id="login" name="<?= \tao_helpers_Uri::encode(\oat\tao\model\auth\BasicAuth::PROPERTY_LOGIN) ?>" class="credential-field"
               value="<?=get_data(\oat\tao\model\auth\BasicAuth::PROPERTY_LOGIN)?>">
    </div>
    <div>
        <label for="password"><?=__('Password')?></label>
        <input type="password" name="<?=\tao_helpers_Uri::encode(\oat\tao\model\auth\BasicAuth::PROPERTY_PASSWORD)?>" autocomplete="off"
               value="<?=get_data(\oat\tao\model\auth\BasicAuth::PROPERTY_PASSWORD)?>"
               class="credential-field">
    </div>
</div>
