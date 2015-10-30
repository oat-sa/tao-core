<ul class="entry-point-box flex-grid plain">
    {{#each list}}
    <li class="entry flex-grid-{{#if width}}{{width}}{{else}}6{{/if}}">
        <a class="block entry-point" href="{{url}}">
            <h3 class="title">{{label}}</h3>
            {{#if content}}<div class="content clearfix">{{{content}}}</div>{{/if}}
            <div class="bottom clearfix">
                <span class="text-link"><span class="icon-play"></span>{{text}}</span>
            </div>
        </a>
    </li>
    {{/each}}
</ul>
