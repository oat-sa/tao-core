<div class="ui-form-field">
    <label
        class="label"
        for="{{input.name}}">
        {{label}}
        {{#if required}}
        <abbr title="This field is required">*</abbr>
        {{/if}}
    </label><!--

    --><select
        class="input"
        name="{{input.name}}">
        <option value=""></option>
        {{#each input.options}}
        <option value="{{uri}}">{{label}}</option>
        {{/each}}
    </select>
</div>