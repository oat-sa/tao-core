<div class="ui-form-field bool-list">
    <label
        class="label form_desc"
        for="{{input.name}}">
        {{label}}
        {{#if required}}
        <abbr title="This field is required">*</abbr>
        {{/if}}
    </label><!--

    --><div class="input form_radlst form_checklst">
        {{#each input.options}}
        <input
            name="{{name}}"
            type="checkbox"
            value="{{value}}">
        &nbsp;
        <label
            class="elt_desc"
            for="{{name}}">
            {{label}}
        </label>
        <br>
        {{/each}}
</div>