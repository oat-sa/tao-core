<div class="ui-form xhtml_form">
    <form
        {{#if action}}
        action="{{action}}"
        {{/if}}
        {{#if method}}
        method="{{method}}"
        {{/if}}
        name="{{name}}">

        <div class="ui-form-field"></div>

        <div class="form-toolbar">
            <button
                type="submit"
                name="{{submit.name}}"
                id="Save"
                class="form-submitter btn-success small"
                value="{{submit.value}}">
                <span class="icon-save"/> Save
            </button>
        </div>

    </form>
</div>