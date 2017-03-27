<div class="ui-form xhtml_form">
    <form
        {{#if action}}
        action="{{action}}"
        {{/if}}
        {{#if method}}
        method="{{method}}"
        {{/if}}
        {{#if name}}
        name="{{name}}"
        {{/if}}>

        <div class="field-container"></div>

        <div class="form-toolbar">
            <button
                class="btn-success small"
                type="submit"
                name="ui-form-submit">
                <span class="icon-save"/> {{submit.value}}
            </button>
        </div>

    </form>
</div>