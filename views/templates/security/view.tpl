<div class="data-container-wrapper flex-container-full">
    <div class="grid-row">
        <div class="col-12">
            <h1><?= __('Security settings'); ?></h1>
        </div>
    </div>
</div>

<header class="section-header flex-container-full">
    <h2><?= get_data('formTitle'); ?></h2>
</header>
<div class="main-container flex-container-main-form">
    <?php if(has_data('cspHeaderForm')): ?>
    <div class="form-content">
        <?= get_data('cspHeaderForm'); ?>
    </div>
    <?php endif;?>
</div>
