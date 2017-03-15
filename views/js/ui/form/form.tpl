<form
    name="{{name}}"
    {{#if method}}
    method="{{method}}"
    {{/if}}
    {{#if action}}
    action="{{action}}"
    {{/if}}
>

    <div class="ui-form-field"></div>

    <div class="form-toolbar">
        <button
            type="submit"
            name="{{'save' + name}}"
            class="form-submitter btn-success small"
            value="Save"
        >
            <span class="icon-save"/> Save
        </button>
    </div>

</form>