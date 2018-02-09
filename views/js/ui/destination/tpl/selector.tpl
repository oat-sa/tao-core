<div class="destination-selector">
    {{#if title}}
    <h2>{{title}}</h2>
    {{/if}}
    <div>
        {{#if description}}
        <p>{{description}}</p>
        {{/if}}
        <div class="selector-container"></div>
        <div class="actions">
            <button class="btn-info small action" disabled><span class="icon-{{icon}}"></span> {{actionName}}</button>
        </div>
    </div>
</div>
