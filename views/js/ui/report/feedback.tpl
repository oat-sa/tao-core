<div class="feedback-{{type}} {{#if hasChildren}}hierarchical{{else}}leaf{{/if}}">
    <span class="icon-{{type}}{{#if hasChildren}} hierarchical-icon{{/if}}"></span>
    <div class="message">{{{message}}}</div>
    {{#each children}}
        {{{this}}}
    {{/each}}
    <div class="actions">
        {{#each actions}}
        <button data-trigger="{{id}}" class="action btn-info" title="{{title}}"><span class="icon-{{icon}}"></span>{{label}}</button>
        {{/each}}
    </div>
</div>