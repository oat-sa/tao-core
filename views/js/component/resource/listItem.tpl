<li data-uri="{{uri}}" {{#if selected}}class="selected"{{/if}}>
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

