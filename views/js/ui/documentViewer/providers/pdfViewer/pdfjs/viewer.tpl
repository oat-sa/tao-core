<div class="pdf-bar">
    <div class="pdf-actions">
        <button class="small navigation" data-control="pdf-page-prev" data-direction="-1" title="{{__ 'Previous page'}}"><span class="icon icon-step-backward"></span><span class="text">{{__ 'Previous'}}</span></button>
        <button class="small navigation" data-control="pdf-page-next" data-direction="1" title="{{__ 'Next page'}}"><span class="icon icon-step-forward"></span><span class="text">{{__ 'Next'}}</span></button>
        {{#if fitToWidth}}
        <input type="checkbox" id="fit-to-width" data-control="fit-to-width" value="1" checked /> <label for="fit-to-width">{{__ "Fit to width"}}</label>
        {{/if}}
    </div>
    <div class="pdf-info">
        <label for="pdf-page-num">{{__ 'Page'}}:</label>
        <input class="info" id="pdf-page-num" data-control="pdf-page-num" value="1" />
        <label>{{__ 'of'}}</label>
        <span class="info" data-control="pdf-page-count">1</span>
    </div>
</div>
<div class="pdf-container"></div>
