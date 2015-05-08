<div class="feedback-{{level}}">
<span class="icon-lock {{level}}-color {{level}}"></span>
    {{msg}}
    <div class="txt-rgt button-container">
        <button class="btn btn-info small check-in"><span class="icon-unlock info"></span>{{__ 'Check-in'}}</button>
        <button class="btn btn-info small release"><span class="icon-result-nok info"></span>{{__ 'Release'}}</button>
    </div>

    <div id="message-container" class="button-container" style="display: none;">
            <label>
                {{__ 'Message'}}
            </label>
            <input type="text" name="message" id="message">
            <button class="btn-info small commit">{{__ 'Commit'}}</button>
        </div>
    </div>
</div>
