<div class="grid-row">
    <div class="col-6">Page <strong>{{page}}</strong> of {{total}}</div>
    <div class="col-6 txt-rgt"><button class="btn-info small itemsmgr-backward"><span class="icon-backward"></span>Previous</button><button class="btn-info small itemsmgr-forward">Next<span class="icon-forward r"></span></button></div>
</div>
<table class="matrix">
    <colgroup>
        <col/>
        <col/>
        <col/>
        <col/>
        <col/>
        <col/>
        <col style="width:250px;"/>
    </colgroup>
    <thead>
        <tr>
            <th>{{__ 'Login'}}</th>
            <th>{{__ 'Name'}}</th>
            <th>{{__ 'Mail'}}</th>
            <th>{{__ 'Roles'}}</th>
            <th>{{__ 'Data Language'}}</th>
            <th>{{__ 'Interface Language'}}</th>
            <th>{{__ 'Actions'}}</th>
        </tr>
    </thead>
    <tbody>
        {{#users}}
            <tr>
                <td>{{login}}</td>
                <td>{{name}}</td>
                <td>{{mail}}</td>
                <td>{{roles}}</td>
                <td>{{dataLg}}</td>
                <td>{{guiLg}}</td>
                <td style="text-align:center;" data-user-identifier="{{id}}">
                    {{#each ../actions}}
                        <button class="btn-info small {{this}}"><span class="icon-{{this}}"></span> {{this}}</button>
                    {{/each}}
                </td>
            </tr>
        {{/users}}
    </tbody>
</table>
<div class="grid-row">
    <div class="col-6">Page <strong>{{page}}</strong> of {{total}}</div>
    <div class="col-6 txt-rgt"><button class="btn-info small itemsmgr-backward"><span class="icon-backward"></span>Previous</button><button class="btn-info small itemsmgr-forward"">Next<span class="icon-forward r"></span></button></div>
</div>
