<div class="class-selector">
    {{#if classUri}}
    <a href="#" class="selected truncate" data-uri="{{classUri}}" title="{{label}}">{{label}}</a>
    {{else}}
    <a href="#" class="selected truncate empty">{{placeholder}}</a>
    {{/if}}
    <div class="options folded">
        <ul>{{{tree}}}</ul>
    </div>
</div>
