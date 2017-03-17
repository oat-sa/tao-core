<div class="bool-list">
    <label
        class="form_desc"
        for="{{input.name}}">
        {{label}}
        {{#if required}}
        <abbr title="This field is required">*</abbr>
        {{/if}}
    </label><!--

    --><div class="form_radlst form_checklst">
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