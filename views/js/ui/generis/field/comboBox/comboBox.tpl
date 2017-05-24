<div class="ui-form-field">
    <label
        class="label"
        for="{{uri}}">
        {{label}}
        {{#if required}}
        <abbr title="This field is required">*</abbr>
        {{/if}}
    </label><!--

    --><select
        class="input"
        name="{{uri}}">
        <option value=""></option>
        {{#each values}}
        <option
            value="{{this.uri}}"
            {{#if value === this.uri}}
            selected="selected"
            {{/if}}>
            {{this.label}}
        </option>
        {{/each}}
    </select>
</div>