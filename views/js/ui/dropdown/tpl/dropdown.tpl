<div class="dropdown-container">
    <div class="dropdown {{#if cls}} {{cls}}{{/if}} {{#if isOpen}} open{{/if}}" {{#if id}}id="{{id}}"{{/if}} data-control="{{id}}" role="navigation">
        <div class="dropdown-header a{{#if headerItem.cls}} {{headerItem.cls}}{{/if}}" aria-haspopup="true">
            {{! the list header can contain raw html, or structured data }}
            {{#if headerItem.html}}
            {{{headerItem.html}}}
            {{else}}
            {{#if headerItem.icon}}<span class="icon icon-{{headerItem.icon}}"></span>{{/if}}
            <span class="text">{{headerItem.text}}</span>
            {{/if}}
            <span class="toggler clickable" tabindex="0"></span>
        </div>
        <ul class="dropdown-submenu plain" aria-label="submenu">
            {{#each innerItems}}
            {{! each list item can contain raw html, or structured data }}
            {{#if this.html}}
            {{{this.html}}}
            {{else}}
            <li class="{{#if this.cls}}{{this.cls}}{{/if}}">
                {{#if this.link}}<a href="{{this.link}}"{{else}}<span class="a"{{/if}} tabindex="0">
                    {{#if this.icon}}<span class="icon icon-{{this.icon}}"></span>{{/if}}
                    <span class="text">{{this.text}}</span>
                {{#if this.link}}</a>{{else}}</span>{{/if}}
            </li>
            {{/if}}
            {{/each}}
        </ul>
    </div>
</div>
