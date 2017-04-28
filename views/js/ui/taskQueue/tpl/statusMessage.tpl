<div class="message">
    {{#if name}}
        {{__ "Status of"}} <span class="task-name">{{name}}</span>
    {{else}}
        {{__ "Status"}}
    {{/if}}
    : <span class="task-status">{{status}}</span>
</div>