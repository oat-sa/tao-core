<div class="dropdown-container">
    <div class="dropdown {{cls}}{{#if isOpen}} open{{/if}}" id="{{id}}" data-control="{{id}}" role="navigation">
        <div class="dropdown-header a toggler" aria-haspopup="true" tabindex="0">
            {{! the list header should contain raw html }}
            {{{data.headerItem}}}
        </div>
        <ul class="dropdown-submenu plain" aria-label="submenu">
        </ul>
    </div>
</div>
