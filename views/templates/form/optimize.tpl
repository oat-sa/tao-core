<script type="text/javascript" src="<?=get_data('ROOT_URL')?>/tao/views/js/Switcher.js"></script>

<div id="compilation-container">
        <input type="button" value="optimize" id="compileButton"/>

        <div id="compilation-grid-container">
                <table id="compilation-grid"/>
        </div>
</div>

<script type="text/javascript">
        $(function(){
             $('#compileButton').click(function(){
                        var mySwitcher = new switcherClass('compilation-grid');
                        mySwitcher.init();
             });
             
        });
</script>