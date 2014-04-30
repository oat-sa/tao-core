<div class="resourcemgr modal">


<!-- left section: items selection -->
    <section class="file-browser">
        
        <h1>{{ __ 'Browse resources'}}</h1>
        <ul class="folders"></ul>
    </section>
 
<!-- test editor  -->
    <section class="file-selector">
        <h1>
            <div class="title"></div> 
            <div class="upload-switcher">
                <span class="icon-add"></span>{{__ 'Upload'}}
            </div>
        </h1>

        <ul class="files"></ul>
        
        <div class="uploader">
            <div id="dragZone">
<!-- see http://www.sitepoint.com/html5-file-drag-and-drop/  -->
            </div>
            <div class="progressbar"></div>
            <div class="file-upload grid-row">
                <span class="btn-info small col-4">
                    <span class="icon-import"></span>
                    {{__ 'Upload'}}
                </span>
                <span class="file-name col-8 truncate"></span>
                <input type="file">
            </div>
        </div>
    </section>   

    <section class="file-preview">
        <h1>{{__ 'Preview'}}</h1>
        <div class="previewer">
            <p class="nopreview">NO PREVIEW</p>
        </div>
       
        <h2 class="toggler" data-toggle="~ .file-properties">{{__ 'File Properties'}}</h2>
        <div class="file-properties">

            <div class="grid-row">
                <div class="col-2">
                    {{__ 'Type'}}
                </div>
                <div class="col-10">
                    audio/ogg
                </div>
            </div>

            <div class="grid-row">
                <div class="col-2">
                    {{__ 'Size'}}
                </div>
                <div class="col-10">
                    32Mb
                </div>
            </div>
            
            <div class="grid-row">
                <div class="col-2">
                    {{__ 'URL'}}
                </div>
                <div class="col-10">
                    <a href="#">http://tao.local/test/1234/rammstein.ogg</a>
                </div>
            </div>
        </div>

        <h2 class="toggler" data-toggle="~ .actions">{{__ 'Actions'}}</h2>
        <div class="action>">
            <button class="btn-success small select">
                <span class="icon-move-item"></span>{{__ 'Select'}}
            </button>
        </div>
    </section>
</div>
