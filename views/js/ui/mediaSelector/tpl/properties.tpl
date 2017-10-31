{{#if label}}
<div class="grid-row">
    <div class="col-3">
        {{__ 'Name'}}
    </div>
    <div class="col-9">{{label}}</div>
</div>
{{/if}}
{{#if alt}}
<div class="grid-row">
    <div class="col-3">
        {{__ 'Alt text'}}
    </div>
    <div class="col-9">{{alt}}</div>
</div>
{{/if}}
{{#if mime}}
<div class="grid-row">
    <div class="col-3">
        {{__ 'Type'}}
    </div>
    <div class="col-9">{{mime}}</div>
</div>
{{/if}}
{{#if fileSize}}
<div class="grid-row">
    <div class="col-3">
        {{__ 'File Size'}}
    </div>
    <div class="col-9">{{fileSize}}</div>
</div>
{{/if}}
{{#if size}}
<div class="grid-row">
    <div class="col-3">
        {{__ 'Size'}}
    </div>
    <div class="col-9">{{size}}</div>
</div>
{{/if}}
