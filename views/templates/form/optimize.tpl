<script type="text/javascript" src="<?=get_data('ROOT_URL')?>/tao/views/js/Switcher.js"></script>

<div id="compilation-container">
        <input type="button" value="optimize" id="compileButton"/>

        <div id="compilation-grid-container" style="display:none">
                <table id="compilation-grid"/>
                
                <input type="button" value="force recompilation" id="recompileButton"/>
        </div>
</div>

<script type="text/javascript">
        $(function(){
             var mySwitcher = new switcherClass('compilation-grid');
             $('#compileButton').click(function(){
                        mySwitcher.init();
                        $('#compilation-grid-container').show();
             });
             
             $('#recompileButton').click(function(){
                        //var mySwitcher = new switcherClass('compilation-grid');
                        mySwitcher.init(true);
             });
             
        });
</script>