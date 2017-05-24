<div class="ui-form-field">
    <label
        class="label"
        for="{{uri}}">
        {{label}}
        {{#if required}}
        <abbr title="This field is required">*</abbr>
        {{/if}}
    </label><!--

    --><div class="input checkbox">
        {{#each values}}
        <div>
            <input
                name="{{this.uri}}"
                type="checkbox"
                value="{{this.uri}}"
                {{#if value === this.uri}}
                selected="selected"
                {{/if}}>
            &nbsp;
            <label
                for="{{this.uri}}">
                {{this.label}}
            </label>
        </div>
        {{/each}}
    </div>
</div>