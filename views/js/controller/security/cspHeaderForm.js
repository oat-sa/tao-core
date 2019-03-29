define([
    'jquery',
    'lodash',
    'i18n',
    'ui/feedback',
    'ui/tooltip'
],
function($, _, __, feedback, tooltip) {
    'use strict';


    function initializeForm() {
        var $form = $('form#cspHeader'),
            $formSourceList = $form.find('#iframeSourceDomains').parent(),
            $formRadioOptions = $form.find('input[name=iframeSourceOption]'),
            $selectedRadio;

        // manage radios & visibility of form sections:
        $formSourceList.hide();

        $selectedRadio = $form.find('input[name=iframeSourceOption]:checked');
        if ($selectedRadio.val() === 'list') {
            $formSourceList.show();
        }

        $formRadioOptions.on('click', function() {
            var selectedValue = $(this).val();
            $formSourceList.toggle(selectedValue === 'list');
        });

        // handle submit:
        $form.on('submit', _submitForm);
    }

    function _submitForm(event) {
        var $form = $('form#cspHeader');
        event.preventDefault();

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            success: function(data) {
                $form.closest('.content-block').html(data);
                setTimeout(_showFeedback, 250);
            },
            fail: function() {
                feedback().error(__('Form data not saved.'));
            }
        });
    }

    function _showFeedback() {
        // DOM content was probably renewed, so make sure we have a fresh reference:
        var $form = $('form#cspHeader');
        var $formTextArea = $form.find('#iframeSourceDomains');
        var $formErrors = $form.find('.form-error');
        var tooltipOptions;

        // handle errors:
        if ($formErrors.length > 0) {
            tooltipOptions = {
                trigger: 'click',
                closeOnClickOutside: true,
                placement: 'right'
            };
            tooltip.error($formTextArea, $formErrors.html(), tooltipOptions).show();
            $formErrors.remove();
        }
        else  {
            feedback().success(__('Saved.'));
        }
    }

    return {
        start : initializeForm
    };
});
