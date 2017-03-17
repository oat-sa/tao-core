<div>
    <label
        class="form_desc"
        for="{{input.name}}">
        {{label}}
        {{#if required}}
        <abbr title="This field is required">*</abbr>
        {{/if}}
    </label><!--

    --><select name="{{input.name}}">
        {{#each input.options}}
        <option value="{{value}}">{{label}}</option>
        {{/each}}
    </select>
</div>