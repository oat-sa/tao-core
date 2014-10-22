<div class="grid-row">
    <div class="col-6">Page <strong>{{page}}</strong> of {{total}}</div>
    <div class="col-6 txt-rgt"><button class="btn-info small itemsmgr-backward"><span class="icon-backward"></span>Previous</button><button class="btn-info small itemsmgr-forward">Next<span class="icon-forward r"></span></button></div>
</div>
<table class="matrix itemsmgr">
    <colgroup>
        <col/>
        {{#model}}
        <col/>
        {{/model}}
        {{#if actions}}
        <col style="width:250px"/>
        {{/if}}
    </colgroup>
    <thead>
        <tr>
            <th class="id"></th>
        {{#model}}
            <th {{#if sortable}}data-sort-by="{{id}}"{{/if}}>{{label}}</th>
        {{/model}}
        </tr>
    </thead>
    <tbody>
        {{#data}}
            <tr>
                {{#each this}}
                <td class="{{@key}}">{{this}}</td>
                {{/each}}
                <td style="text-align:center;" data-item-identifier="{{id}}">
                    {{#each ../actions}}
                    <button class="btn-info small {{this}}"><span class="icon-{{this}}"></span> {{this}}</button>
                    {{/each}}
                </td>
            </tr>
        {{/data}}
    </tbody>
</table>
<div class="grid-row">
    <div class="col-6">Page <strong>{{page}}</strong> of {{total}}</div>
    <div class="col-6 txt-rgt"><button class="btn-info small itemsmgr-backward"><span class="icon-backward"></span>Previous</button><button class="btn-info small itemsmgr-forward"">Next<span class="icon-forward r"></span></button></div>
</div>
