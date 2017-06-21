{{#each buttons}}
    <button class="btn-{{type}} small {{id}}" data-control="{{id}}" type="button">
        {{#if icon}}<span class="icon-{{icon}}"></span> {{/if}}
        <span class="label">{{label}}</span>
    </button>
{{/each}}
