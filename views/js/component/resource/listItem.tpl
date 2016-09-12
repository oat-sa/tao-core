<li data-uri="{{uri}}">
    <strong>{{label}}</strong>
    {{#if desc}}
    <em>{{desc}}</em>
    {{/if}}
    {{#if childList}}
        <ul>
            {{{childList}}}
        </ul>
    {{/if}}
</li>

