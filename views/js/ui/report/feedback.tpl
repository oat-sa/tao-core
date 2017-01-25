<div class="feedback-{{type}} {{#if hasChildren}}hierarchical{{else}}leaf{{/if}}">
    <span class="icon-{{type}}{{#if hasChildren}} hierarchical-icon{{/if}}"></span>
    <span class="message">{{message}}</span>
    {{#each children}}
        {{{this}}}
    {{/each}}
</div>