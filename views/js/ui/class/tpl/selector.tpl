<div class="class-selector">
    {{#if classUri}}
    <a href="#" class="selected" data-uri="{{classUri}}">{{label}}</a>
    {{else}}
    <a href="#" class="selected empty" data-uri>{{placeholder}}</a>
    {{/if}}
    <div class="options folded">
        <ul>{{{tree}}}</ul>
    </div>
</div>
