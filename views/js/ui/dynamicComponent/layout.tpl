<div class="dynamic-component-container">
    <div class="dynamic-component-title-bar">
        <h3>{{title}}</h3>
        <a title="{{__ "close"}}" class="closer" href="#"></a>
    </div>
    <div class="dynamic-component-content">
        {{#if draggableContainer}}
        <div class="dynamic-component-layer"></div>
        {{/if}}
    </div>
</div>