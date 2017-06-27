<div class="ui-form-field">
    <label
        class="label"
        for="{{input.name}}">
        {{label}}
        {{#if required}}
        <abbr title="This field is required">*</abbr>
        {{/if}}
    </label><!--

    --><input
        class="input"
        name="{{input.name}}"
        type="text"
        value="{{input.value}}">
</div>