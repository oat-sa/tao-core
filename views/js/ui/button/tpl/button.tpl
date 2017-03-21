<button class="{{#if type}}btn-{{type}}{{/if}}{{#if small}} small{{/if}}{{#if cls}} {{cls}}{{/if}}" data-control="{{id}}"{{#if title}} title="{{title}}"{{/if}}>
    {{#if icon}}<span class="icon icon-{{icon}}"></span>{{/if}}
    <span class="label">{{label}}</span>
</button>
