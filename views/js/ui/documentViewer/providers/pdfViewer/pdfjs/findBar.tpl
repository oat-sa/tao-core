<button class="small search" data-control="pdf-search" title="{{__ 'Search'}}"><span class="icon icon-find"></span></button>
<div class="pdf-find-bar hidden">
    <label for="pdf-search-query">{{__ 'Search'}}:</label>
    <input type="text" id="pdf-search-query" data-control="pdf-search-query" placeholder="{{__ 'Type your search'}}"/>
    <button class="small search" data-control="pdf-search-prev" title="{{__ 'Previous match'}}"><span class="icon icon-left"></span></button>
    <button class="small search" data-control="pdf-search-next" title="{{__ 'Next match'}}"><span class="icon icon-right"></span></button>
    <input type="checkbox" id="highlight-all" data-control="highlight-all" value="1" {{#if highlightAll}}checked {{/if}}/><label for="highlight-all">{{__ "Highlight all"}}</label>
    <input type="checkbox" id="case-sensitive-search" data-control="case-sensitive-search" value="1" {{#if caseSensitive}}checked {{/if}}/><label for="case-sensitive-search">{{__ "Case sensitive"}}</label>
    <span class="pdf-search-info" data-control="pdf-search-info">
        <span class="pdf-search-position hidden" data-control="pdf-search-position">
            <span class="pdf-search-index" data-control="pdf-search-index"></span>/<span class="pdf-search-count" data-control="pdf-search-count"></span>
        </span>
        <span class="pdf-search-loop hidden" data-control="pdf-search-loop-begin">
            {{__ 'End of document reached. Continuing from the beginning.'}}
        </span>
        <span class="pdf-search-loop hidden" data-control="pdf-search-loop-end">
            {{__ 'Start of document reached. Continuing from the end.'}}
        </span>
    </span>
</div>
