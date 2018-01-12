<div class="media-sizer media-sizer-synced">

    <div class="media-sizer-responsive-toggle">
        <label>
        <input type="checkbox" {{#if responsive}}checked="checked"{{/if}} class="media-mode-switch"/>
        <span class="icon-checkbox"></span>
        {{__ 'Responsive mode'}}
        </label>
        <span class="icon-help tooltipstered" data-tooltip="~ .tooltip-content:first" data-tooltip-theme="warning"></span>

        <div class="tooltip-content">
            {{__ 'Media resize along with their container, e.g. 50% means half of the container size not half of the media size.'}}
        </div>
    </div>


    <div class="media-sizer-percent">
        <label for="media-sizer-percent-width">{{__ 'Size'}}</label>
        <span class="item-editor-unit-input-box">
            <input type="text" name="width" id="media-sizer-percent-width" data-validate="$numeric" data-validate-option="$allowEmpty;"/>
        </span>

        <div class="media-sizer-reset media-sizer-reset-percent" data-unit="percent">
            <span class="icon-reset"></span>
        </div>

        <div class="media-sizer-slider-box">
            <div class="media-sizer-slider"></div>
        </div>
    </div>

    <div class="media-sizer-pixel">
        <label for="media-sizer-pixel-width">{{__ 'Width'}}</label>
        <span class="item-editor-unit-input-box">
            <input type="text" name="width" id="media-sizer-pixel-width" data-validate="$numeric" data-validate-option="$allowEmpty;"/>
        </span>

        <label for="media-sizer-pixel-height">{{__ 'Height'}}</label>
        <span class="item-editor-unit-input-box">
            <input type="text" name="height" id="media-sizer-pixel-height" data-validate="$numeric" data-validate-option="$allowEmpty;"/>
        </span>

        <div class="media-sizer-sync">
            <span class="icon-link"></span>
        </div>

        <div class="media-sizer-reset media-sizer-reset-pixel" data-unit="pixel">
            <span class="icon-reset"></span>
        </div>

        <div class="media-sizer-slider-box">
            <div class="media-sizer-slider"></div>
            <div class="media-sizer-cover"></div>
        </div>
    </div>
</div>