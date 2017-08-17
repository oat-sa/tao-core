<div class="ui-generis-form">
    <h2>{{title}}</h2>
    <form action="{{formAction}}" method="{{formMethod}}">
        <fieldset></fieldset>
        <div class="toolbar">
            {{#if reset}}
            <button type="reset" class="btn-neutral small">
                <span class="icon-reset"/> {{resetText}}
            </button>
            {{/if}}
            <button type="submit" class="btn-info small">
                <span class="icon-save"/> {{submitText}}
            </button>
        </div>
    </form>
</div>
