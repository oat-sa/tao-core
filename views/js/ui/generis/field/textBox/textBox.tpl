<div class="ui-form-field">
    <label
        class="label"
        for="{{uri}}">
        {{label}}
        {{#if required}}
        <abbr title="This field is required">*</abbr>
        {{/if}}
    </label><!--

    --><input
        class="input"
        name="{{uri}}"
        type="text"
        value="{{value}}">
</div>