<div class="media-selector">
    <section class="media-browser">
        <div class="resource-selector-container"></div>
        <ul class="actions plain action-bar tree-action-bar">
            <li class="action"><a href="#" class="li-inner" data-action="toggleUpload"><span class="glyph icon-upload"></span>{{__ 'Upload'}}</a></li>
            <li class="action"><a href="#" class="li-inner disabled" data-action="downloadMedia"><span class="glyph icon-download"></span>{{__ 'Download'}}</a></li>
            <li class="action"><a href="#" class="li-inner disabled" data-action="deleteMedia"><span class="glyph icon-bin"></span>{{__ 'Delete'}}</a></li>
            <!--<li class="action"><a href="#" class="li-inner disabled"><span class="glyph icon-move-item"></span>{{__ 'Select'}}</a></li>-->
        </ul>
    </section>

    <section class="media-view {{#if startUploading}}hidden{{/if}}">
        <h2>{{__ 'Preview'}}</h2>
        <div class="media-preview"></div>
        <div class="media-properties"></div>
        <div class="actions">
            <a href="#" class="btn small btn-success" data-action="select"><span class="glyph icon-move-item"></span> {{__ 'Select'}}</a>
        </div>
    </section>
    <section class="media-upload {{#unless startUploading}}hidden{{/unless}}">
        <h2>{{__ 'Upload'}}</h2>
        <p>{{__ 'to'}} : <span class="destination"></span></p>
        <div class="media-uploader"></div>
    </section>

</div>
