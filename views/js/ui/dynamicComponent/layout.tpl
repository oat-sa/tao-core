<div class="dynamic-component-container">
    <div class="dynamic-component-title-bar">
        <i class="title-bar-icon"></i>
        <a title="{{__ "Close"}}" class="closer" href="#"></a>
    </div>
    <div class="dynamic-component-content">
        {{#if draggableContainer}}
        <div class="dynamic-component-layer"></div>
        {{/if}}
    </div>
    {{#if resizable}}
        <div class="dynamic-component-resize-container">
            <div class="dynamic-component-resize-wrapper">
                <div class="dynamic-component-resize"></div>
            </div>
        </div>
    {{/if}}
</div>
