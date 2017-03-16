<div>
    <label
        class="form_desc"
        for="{{input.name}}">
        {{label}}
        {{#if required}}
        <abbr title="This field is required">*</abbr>
        {{/if}}
    </label><!--

    --><input
        name="{{input.name}}"
        type="text"
        value="{{input.value}}">
</div>