<div class="bulk-action-popup">
    <h2 class="title">{{__ "Action"}}: {{actionName}}</h2>
    
    {{#if single}}
    <div class="single" data-resource="{{allowedResources.0.id}}">
        <p>
            The action will be applied to the {{resourceType}} <span class="resource-label">{{allowedResources.0.label}}</span>
        </p>
    </div>
    {{else}}
    <div class="multiple">
        <p>
            The action will be applied to the following <span class="resource-count">{{resourceCount}}</span> {{resourceType}}s:
        </p>
        <ul class="plain applicables">
            {{#each allowedResources}}
            <li data-resource="{{id}}">
                <span class="resource-label">{{label}}</span>
            </li>
            {{/each}}
        </ul>
    </div>
    {{/if}}
    
    {{#if deniedResources.length}}
    <p>
        However, the action does not apply to the following {{resourceType}}s:
    </p>
    <ul class="plain no-applicables">
        {{#each deniedResources}}
        <li data-resource="{{id}}">
            <span class="resource-label">{{label}}</span>
            <span class="reason">({{reason}})</span>
        </li>
        {{/each}}
    </ul>
    {{/if}}
    
    {{#if reason}}
    <div class="reason">
        <p>
            {{__ "Please provide a reason"}}:
        </p>
        <div class="categories"></div>
        <div class="comment">
            <textarea placeholder="{{__ "comment..."}}"></textarea>
        </div>
    </div>
    <div class="actions">
        <button class="btn btn-info small done">{{__ "OK"}}</button>
        <a href="#" class="btn cancel" title="{{__ "cancel the action"}}">{{__ "cancel"}}</a>
    </div>
    {{/if}}
</div>