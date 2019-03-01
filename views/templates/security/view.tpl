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

<?php if (get_data('cspHeaderFormSuccess')): ?>
<div id="csp-header-success" class="hidden"><?= get_data('cspHeaderFormSuccess') ?></div>
<?php endif;?>

<script>
    require(
        [
            'jquery',
            'ui/feedback',
            'ui/tooltip',
            'uiForm'
        ],
        function($, feedback, tooltip, uiForm) {
            'use strict';

            var $form = $('form#cspHeader'),
                $formSubmitButton = $form.find('#Save'),
                $formTextArea = $form.find('#iframeSourceDomains'),
                $textAreaLabel = $form.find('label[for=iframeSourceDomains]'),
                $formRadioOptions = $form.find('input[name=iframeSourceOption]'),
                $formErrors = $form.find('.form-error'),
                $formSuccess = $('#csp-header-success');

            $(document).ready(function() {

                initializeForm();

                $formRadioOptions.on('click', function() {
                    var selectedValue = $(this).val();
                    if (selectedValue === 'list') {
                        showSourceList();
                    } else {
                        hideSourceList();
                    }
                });

                if ($formSuccess.length > 0) {
                    feedback().success($formSuccess.html());
                }

                $formSubmitButton.on('click', function() {
                    uiForm.submitForm($form);
                })
            });

            function initializeForm() {
                var selectedRadio = $form.find('input[name=iframeSourceOption]:checked');

                $textAreaLabel.hide();

                if ($formErrors.length > 0) {
                    var tooltipOptions = {
                        trigger: 'manual',
                        closeOnClickOutside: true
                    };

                    tooltip.error($formTextArea, $formErrors.html(), tooltipOptions).show();
                    $formErrors.remove();
                }

                if (selectedRadio.length > 0 && selectedRadio.val() === 'list') {
                    showSourceList();
                }
            }

            function showSourceList() {
                $formTextArea.removeClass('hidden');
                $textAreaLabel.show();
            }

            function hideSourceList() {
                $formTextArea.addClass('hidden');
                $textAreaLabel.hide();
            }
        }
    )
</script>