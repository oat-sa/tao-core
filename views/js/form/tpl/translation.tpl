<div class="translations-container flex-container-full">
    <header class="section-header flex-container-full">
        <h2>{{__ 'Translations'}}</h2>
{{#if languages}}
        <div class="translations-create">
            <label>{{__ 'Language'}}
                <select name="language" data-control="select"></select>
            </label>
            <button class="btn-info small" data-control="create">
                <span class="icon-replace"></span>
                {{__ 'Create translation'}}
            </button>
        </div>
{{/if}}
    </header>
{{#if ready}}
    <div class="translations-list"></div>
{{else}}
    <div class="translations-not-ready">
        {{#if isEmpty}}
            <p>{{__ 'Empty resources cannot be translated.'}}</p>
        {{/if}}
        {{#unless isReadyForTranslation}}
            <p>{{__ 'This resource is not ready for translation.'}}</p>
        {{/unless}}
    </div>
{{/if}}
</div>
