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
            $formRadioOptions = $form.find('input[name=iframeSourceOption]'),
            $formErrors = $form.find('.form-error'),
            $formSuccess = $('#csp-header-success');

        $(document).ready(function() {
            initializeForm();

            $formRadioOptions.on('click', function() {
                var selectedValue = $(this).val();
                if (selectedValue === 'list') {
                    showTextArea();
                } else {
                    hideTextArea();
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

            hideTextArea();

            if ($formErrors.length > 0) {
                var tooltipOptions = {
                    trigger: 'manual',
                    closeOnClickOutside: true
                };

                tooltip.error($formTextArea, $formErrors.html(), tooltipOptions).show();
                $formErrors.remove();
            }

            if (selectedRadio.length > 0 && selectedRadio.val() === 'list') {
                showTextArea();
            }
        }

        function showTextArea() {
            $formTextArea.parent().show();
        }

        function hideTextArea() {
            $formTextArea.parent().hide()
        }
    }
);