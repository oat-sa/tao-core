<?php
use oat\tao\helpers\Template;
?>
<div class="form-content">
    <?=get_data('myForm')?>
</div>

<?php if(has_data('results')):?>
<script>
requirejs.config({
    config : {
        'layout/search' : {
            'result': {
                'model' : <?=json_encode(get_data('model'))?>,
                'filters' : <?=json_encode(get_data('filters'))?>,
                'params' : <?=json_encode(get_data('params'))?>,
                'url': <?=json_encode(_url('searchResults', null, null, array('classUri' => get_data('classUri'))))?> 
            }
        }
    }
});
</script>
<?php endif; ?>
