<div class="pdf-bar">
    <div class="pdf-actions">
        <button class="small navigation" data-control="pdf-page-prev" data-direction="-1"><span class="icon icon-step-backward"></span><span class="text">{{__ 'Previous'}}</span></button>
        <button class="small navigation" data-control="pdf-page-next" data-direction="1"><span class="icon icon-step-forward"></span><span class="text">{{__ 'Next'}}</span></button>
    </div>
    <div class="pdf-infos">
        {{__ 'Page'}}:
        <input class="info" data-control="pdf-page-num" value="1" />
        {{__ 'on'}}
        <span class="info" data-control="pdf-page-count">1</span>
    </div>
</div>
<canvas data-control="pdf-content"></canvas>
