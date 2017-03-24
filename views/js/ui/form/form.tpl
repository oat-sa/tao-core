<div id="da_form" class="ui-form xhtml_form">
    <form
        {{#if action}}
        action="{{action}}"
        {{/if}}
        {{#if method}}
        method="{{method}}"
        {{/if}}
        name="{{name}}">

        <!--<div class="field-container"></div>-->

        <div class="ui-form-toolbar form-toolbar">
            <button
                class="btn-success small"
                type="submit"
                name="{{submit.name}}"
                id="Save"
                value="{{submit.value}}">
                <span class="icon-save"/> Save
            </button>
        </div>

    </form>
</div>