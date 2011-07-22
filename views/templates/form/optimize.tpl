<script type="text/javascript" src="<?=ROOT_URL?>/tao/views/js/Switcher.js"></script>
<link rel="stylesheet" href="<?=ROOT_URL?>/tao/views/css/optimize.css" type="text/css" />

<div id="compilation-title" class="ui-widget-header ui-corner-top ui-state-default">
	<?=__("TAO optimizer")?>
</div>
<div id="compilation-container" class="ui-widget-content ui-corner-bottom">
        
        <div id="compilation-compile-button-container" class="ext-home-container ui-state-highlight">
                <span><?=__('Optimize your TAO when you are ready for test delivery')?></span>:&nbsp;
                <input type="button" value="optimize" id="compileButton"/>
        </div>
        
        <div id="compilation-grid-container">
                <div id="compilation-table-container">
                        <table id="compilation-grid" />
                </div>
                <div id="compilation-recompile-button-container">
                        <input type="button" value="force recompilation" id="recompileButton"/>
                        <input type="button" value="decompile" id="decompileButton"/>
                </div>
        </div>
        
        <div id="compilation-grid-results" class="ext-home-container ui-state-highlight"/>
</div>

<script type="text/javascript">
        $(function(){
             var options = {
                     onStart:function(){
                             $('#compilation-grid-container').show();
                     },
                     onStartEmpty:function(){
                             $('#compilation-grid-results').show().html('<?=__('There is no class available for optimization for the current extension')?>');
                     },
                     onStartDecompile:function(){
                             $('#compilation-grid-container').show();
                     },
                     beforeComplete: function(){
                             $('#compilation-grid-results').show().html('<?=__('Rebuilding indexes, it may take a while.')?>');
                     },
                     onComplete:function(switcher, success){
                             if(success){
                                     $('#compilation-grid-results').show().html('<?=__('Compilation completed')?>');
                             }else{
                                      $('#compilation-grid-results').show().html('<?=__('Cannot successfully build the optimized table indexes')?>');
                             }
                             
                     },
                     onCompleteDecompile:function(){
                                $('#compilation-grid-results').show().html('<?=__('Decompilation completed')?>');
                     }
             }
             
             var mySwitcher = new switcherClass('compilation-grid', options);
             $('#compileButton').click(function(){
                        mySwitcher.init();
                        $('#compileButton').hide();
             });
             
             $('#recompileButton').click(function(){
                        mySwitcher.init(true);
                        $('#compilation-grid-results').hide();
             });
             
             $('#decompileButton').click(function(){
                        if(confirm('This action will reset the optimization, are you sure?')){
                                mySwitcher.init(true, true);
                                $('#compilation-grid-results').hide();
                        }
             });
        });
</script>