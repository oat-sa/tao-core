<div class="ui-form">
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

        <div class="toolbar">
            <button
                class="btn-submit btn-success small"
                type="submit"
                name="ui-form-submit">
                <span class="icon-save"/> {{submit.value}}
            </button>
        </div>

    </form>
</div>