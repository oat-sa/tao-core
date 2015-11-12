<div>
    <h1>{{__ "Action"}}: {{actionName}}</h1>
    {{#if single}}
    <div class="single" data-resource="{{allowedResources.0.id}}">
        <p>
        The action will be applied to the {{resourceType}} <span class="resource-label">{{allowedResources.0.label}}</span>
        </p>
    </div>
    {{else}}
    <div class="multiple">
        <p>
        The action will be applied to the following {{resourceType}}s:
        </p>
        <ul class="plain applicables">
            {{#each allowedResources}}
            <li data-resource="{{id}}">
                <span class="resource-label">{{label}}</span>
            </li>
            {{/each}}
        </ul>
        {{#if deniedResources.length}}
        <p>
        However, the action does not applied to the following {{resourceType}}s:
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
    </div>
    {{/if}}
    <div class="reason">
        <div class="categories"></div>
        <div class="comment">
            <label>{{__ "Comment"}}</label>
            <textarea></textarea>
        </div>
    </div>
</div>