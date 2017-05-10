<div class="component-report{{#if noBorder}} no-border{{/if}}">
    {{#if detailsButtonVisible}}
    <label class="fold pseudo-label-box">
        <span class="check-txt hide">{{__ "Hide detailed report"}}</span>
        <span class="check-txt show">{{__ "Show detailed report"}}</span>
        <input type="checkbox"/>
        <span class="icon-checkbox"></span>
    </label>
    {{/if}}
    <div class="content">{{content}}</div>
</div>