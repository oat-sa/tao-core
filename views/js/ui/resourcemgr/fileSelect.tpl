{{#each files}}
<li data-type="{{type}}">
    <span class="desc">{{name}}</span>
    <div class="actions">
        <div class="tlb">
            <div class="tlb-top">
                <span class="tlb-box">
                    <span class="tlb-bar">
                        <span class="tlb-start"></span>
                        <span class="tlb-group">
                            <a href="#" class="tlb-button-off" title="{{__ 'Download'}}"><span class="icon-export"></span></a>
                            <a href="#" class="tlb-button-off" title="{{__ 'Preview'}}"><span class="icon-preview"></span></a>
                            <span class="tlb-separator"></span> 
                            <a href="#" class="tlb-button-off" title="{{__ 'Remove file'}}"><span class="icon-bin"></span></a>
                        </span>
                        <span class="tlb-end"></span>
                    </span>  
                </span>   
            </div>
        </div>
    </div>
</li>
{{/each}}
