<ul class="dashboard-metrics_list {{layoutType}}">
    {{#each data}}
        <li class="dashboard-metric">
            <h4 class="dashboard-metric_title">{{title}}</h4>
            <div class="dashboard-metric_score-container">
                <div class="dashboard-metric_score score-{{state}}" style="width: {{score}}%;"></div>
            </div>
            <ul class="dashboard-metric_info">
                {{#each info}}
                    <li class="dashboard-metric_info-item">{{text}}</li>
                {{/each}}
            </ul>
        </li>
    {{/each}}
</ul>
