{{#equal type 'class'}}
<li data-uri="{{uri}}" class="class {{#equal state 'closed'}}closed{{/equal}}" data-count="{{count}}">
    <a href="#" title="{{label}}">{{label}}</a>
   {{#if count}}
   <ul>
    {{#if childList}}
        {{{childList}}}
    {{/if}}
   </ul>
   {{/if}}
</li>
{{/equal}}

{{#equal type 'instance'}}
<li data-uri="{{uri}}" class="instance">
    <a href="#" title="{{label}}">{{label}}</a>
</li>
{{/equal}}


