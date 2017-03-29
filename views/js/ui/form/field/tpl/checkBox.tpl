<div class="ui-form-field">
    <label
        class="label"
        for="{{input.name}}">
        {{label}}
        {{#if required}}
        <abbr title="This field is required">*</abbr>
        {{/if}}
    </label><!--

    --><div class="input checkbox">
        {{#each input.options}}
        <div>
            <input
                name="{{uri}}"
                type="checkbox"
                value="{{uri}}">
            &nbsp;
            <label
                for="{{uri}}">
                {{label}}
            </label>
        </div>
        {{/each}}
</div>