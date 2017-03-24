<div class="ui-form-field">
    <label
        class="label form_desc"
        for="{{input.name}}">
        {{label}}
        {{#if required}}
        <abbr title="This field is required">*</abbr>
        {{/if}}
    </label><!--

    --><input
        class="input"
        name="{{input.name}}"
        type="password">
</div>

<div class="ui-form-field">
    <label
        class="label form_desc"
        for="{{input.name}}_confirm">
        {{label}} Confirm
        {{#if required}}
        <abbr title="This field is required">*</abbr>
        {{/if}}
    </label><!--

    --><input
        class="input"
        name="{{input.name}}_confirm"
        type="password">
</div>