/*
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2015-2019 (original work) Open Assessment Technologies SA ;
 *
 */

/**
 * UiForm class enable you to manage form elements, initialize form component and bind common events
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
 define([
    'module',
    'jquery',
    'lodash',
    'i18n',
    'helpers',
    'context',
    'form/property',
    'form/post-render-props',
    'form/depends-on-property',
    'util/encode',
    'ckeditor',
    'ui/ckeditor/ckConfigurator',
    'ui/datetime/picker',
    'ui/dialog/confirmDelete',
    'core/request',
    'util/url',
], function (
    module,
    $,
    _,
    __,
    helpers,
    context,
    property,
    postRenderProps,
    dependsOn,
    encode,
    ckeditor,
    ckConfigurator,
    dateTimePicker,
    confirmDialog,
    request,
    urlUtil,
) {
    'use strict';

    /**
     * Create a URL based on action and module
     *
     * @param action
     * @returns {string}
     */
    var getUrl = function getUrl(action) {
        var conf = module.config();
        return context.root_url + conf.extension + '/' + conf.module + '/' + action;
    };

    var UiForm = {

        /**
         * Keep references to CkEditor instances, per field
         */
        htmlEditors : {},

        init: function init() {
            var self = this;

            $('body').off('change', 'input[value=notEmpty]').on('change', 'input[value=notEmpty]', function(event) {
                let primaryPropertyUri = $(event.target).closest('[id^="property_"]').attr('id').replace('property_', '');
                const secondaryProperties = $(`option[value=${primaryPropertyUri}][selected='selected']`).closest('[id^="property_"]');
                let secondaryPropertiesCheckbox = secondaryProperties.find('[value=notEmpty]');

                secondaryPropertiesCheckbox.each((i, notEmptyCheckbox) => {
                    if (event.target.checked) {
                        notEmptyCheckbox.disabled = false;
                    } else {
                        notEmptyCheckbox.disabled = true;
                        notEmptyCheckbox.checked = false;
                    }
                })
            });

            this.counter = 0;
            this.initGenerisFormPattern = new RegExp(['add', 'edit', 'mode', 'PropertiesAuthoring'].join('|'), 'i');
            this.initTranslationFormPattern = /translate/;
            this.htmlEditors = {};

            $(document).ajaxComplete(function (event, request, settings) {
                var testedUrl;

                //initialize regarding the requested action
                //async request waiting for html or not defined
                if (settings.dataType === 'html' || !settings.dataType) {
                    if (settings.url.indexOf('?') !== -1) {
                        testedUrl = settings.url.substr(0, settings.url.indexOf('?'));
                    }
                    else {
                        testedUrl = settings.url;
                    }

                    self.initRendering();
                    self.initElements();
                    if (self.initGenerisFormPattern.test(testedUrl)) {
                        self.initOntoForms();
                    }
                    if (self.initTranslationFormPattern.test(testedUrl)) {
                        self.initTranslationForm();
                    }
                }
            });
            this.initRendering();
        },

        /**
         * make some adjustment on the forms
         */
        initRendering: function initRendering() {

            var self = this;

            var $container          = $('.content-block .xhtml_form:first'),
                $toolBar            = $container.find('.form-toolbar'),
                $authoringBtn       = $('.authoringOpener'),
                $authoringBtnParent,
                $testAuthoringBtn   = $('.test-authoring'),
                $rdfImportForm      = $('.rdfImport #import'),
                $rdfExportForm      = $('.rdfExport #export');

            // allows to fix label position for list of radio buttons
            $('.form_desc ~.form_radlst').parent().addClass('bool-list');

            // allows long labels if the following input is hidden
            $('.form_desc + input[type="hidden"]').prev().addClass('hidden-input-label');

            // move authoring button to toolbar, unless it is already there
            if($authoringBtn.length && !$authoringBtn.hasClass('btn-info')) {
                $authoringBtnParent = $authoringBtn.parent();
                $authoringBtn.prepend($('<span>', { 'class': 'icon-edit' }));
                $authoringBtn.addClass('btn-info small');
                $authoringBtn.appendTo($toolBar);
                $authoringBtnParent.remove();
            }

            // move test authoring button
            if($testAuthoringBtn.length) {
                $testAuthoringBtn.prependTo($toolBar);
            }

            // import Ontology styling changes
            if($rdfImportForm.length) {
                $('span.form_desc:empty',$rdfImportForm).hide();
                $('span.form-elt-info',$rdfImportForm).css({
                    display: 'block',
                    width: '100%'
                });
                $('.form-elt-container.file-uploader',$rdfImportForm).css({
                    width: '65%',
                    float: 'right'
                });

            }
            if($rdfExportForm.length){
                $('div:first',$rdfExportForm).find('input[type="text"]').css('width', 'calc(65% - 23px)');
                $('div:not(.form-toolbar):last span',$rdfExportForm).css('float', 'right')
                                                                    .closest('div')
                                                                    .find('[id*="ns_filter"]')
                                                                    .addClass('btn-default small');
            }

            $('body').off('submit','.xhtml_form form').on('submit', '.xhtml_form form', function (e) {
                var $form = $(this);
                e.preventDefault();

                return self.submitForm($form,  self.getFormData($form));
            });

            $('.form-submitter').off('click').on('click', function (e) {
                e.preventDefault();
                $(this).addClass('current-submitter');
                $(e.target).closest('.xhtml_form form').trigger('submit');
            });

            // modify properties
            postRenderProps.init();
        },

        /**
         * Retrieve form fields and pack to internal format for transfering
         * @param {jQueryElement} $form
         * @returns {object|undefined}
         */
        getFormData: function getFormData($form) {
            var formData   = {};
            var clazz      = {};
            var properties = [];
            var indexes    = [];

            //for backward compatibility
            if (!$('[id="tao.forms.class"]').length) {
                return;
            }

            //get all global data
            $('input.global', $form[0]).each(function () {
                var $global = $(this);
                var name = $global.attr('name');
                if (name.indexOf('class_') > -1) {
                    name = name.replace('class_', '');
                    clazz[name] = $global.val();

                }
                else {
                    formData[name] = $global.val();
                }
            });
            if (clazz.length !== 0) {
                formData.class = clazz;
            }

            //get data for each property
            $('.regular-property', $form[0]).each(function () {
                var property = {};
                var name = '',
                    isArray = false;

                //get range on advanced mode
                var range = [];
                $('[id*="http_2_www_0_w3_0_org_1_2000_1_01_1_rdf-schema_3_range-TreeBox"]', this).find('.checked').each(function () {
                    range.push($(this).parent().attr('id'));
                });
                if (range.length !== 0) {
                    property['http_2_www_0_w3_0_org_1_2000_1_01_1_rdf-schema_3_range'] = range;
                }

                $(':input.property', this).each(function () {
                    var $property = $(this);
                    name = $property.attr('name').replace(/(property_)?[^_]+_/, '');

                    isArray = (name.indexOf('[]') === name.length - 2);
                    if ($property.attr('type') === 'checkbox' && isArray) {
                        name = name.substr(0, name.length - 2);
                        if ($property.is(':checked')) {
                            if (!_.isArray(property[name])) {
                                property[name] = [];
                            }
                            property[name].push($property.val());
                        }

                    } else if ($property.attr('type') === 'radio') {
                        if ($property.is(':checked')) {
                            property[name] = $property.val();
                        }
                    }
                    else {
                        property[name] = $property.val();
                    }

                });
                //get data for each index
                $(':input.index', this).each(function () {

                    var i;
                    var found = false;
                    var name = '';
                    var $index = $(this);
                    for (i in indexes) {
                        if (indexes[i] && $index.attr('data-related-index') === indexes[i].uri) {
                            name = $index.attr('name').replace(/(index_)?[^_]+_/, '');
                            if ($index.attr('type') === 'radio' || $index.attr('type') === 'checkbox') {
                                if ($index.is(':checked')) {
                                    indexes[i][name] = $index.val();
                                }
                            }
                            else {
                                indexes[i][name] = $index.val();
                            }

                            found = true;
                        }
                    }
                    if (!found) {
                        var index = {};
                        index.uri = $index.attr('data-related-index');
                        name = $index.attr('name').replace(/(index_)?[^_]+_/, '');
                        if ($index.attr('type') === 'radio') {
                            if ($index.is(':checked')) {
                                index[name] = $index.val();
                            }
                        }
                        else {
                            index[name] = $index.val();
                        }
                        indexes.push(index);
                    }


                });
                //add indexes to related property
                property.indexes = indexes;
                properties.push(property);
            });

            formData.properties = properties;

            return formData;
        },

        initElements: function initElements() {
            var self = this;
            var $uriElm;
            var $classUriElm;

            //revert form button
            $(".form-refresher").off('click').on('click', function () {
                var $form = $(this).parents('form');
                $(":input[name='" + $form.attr('name') + "_sent']").remove();

                return $form.submit();
            });

            //translate button
            $uriElm      = $("#uri"),
            $classUriElm = $("#classUri");

            $(".form-translator").off('click').on('click', function () {
                if ( $uriElm.length && $classUriElm.length) {
                    helpers.getMainContainer().load(getUrl('translateInstance'), {'uri': $uriElm.val(), 'classUri': $classUriElm.val()});
                }
                return false;
            });

            //map the wysiwyg editor to the html-area fields
            $('.html-area').each(function () {
                var propertyUri = this.id;

                // destroy previously created editors
                if (ckeditor.instances[propertyUri]) {
                    ckeditor.instances[propertyUri].destroy(this);
                    delete self.htmlEditors[propertyUri];
                }

                var editor = ckeditor.replace(this);
                editor.config = ckConfigurator.getConfig(editor, 'htmlField', {resize_enabled : false });
                self.htmlEditors[propertyUri] = editor;
            });

            $('.datepicker-input').each(function(){
                dateTimePicker($(this).parent(), {
                    replaceField : this,
                    setup : 'datetime',
                    controlButtons : true
                });
            });

            $('.box-checker').off('click').on('click', function () {
                var $checker = $(this);
                var regexpId = new RegExp('^' + $checker.prop('id').replace('_checker', ''), 'i');

                if ($checker.hasClass('box-checker-uncheck')) {
                    $(":checkbox:not(:disabled)").each(function () {
                        if (regexpId.test(this.id)) {
                            //noinspection JSPotentiallyInvalidUsageOfThis,JSPotentiallyInvalidUsageOfThis
                            this.checked = false;
                            $(this).change();
                        }
                    });
                    $checker.removeClass('box-checker-uncheck');
                    $checker.text(__('Check all'));
                }
                else {
                    $(":checkbox:not(:disabled)").each(function () {
                        if (regexpId.test(this.id)) {
                            this.checked = true;
                            $(this).change();
                        }
                    });
                    $checker.addClass('box-checker-uncheck');
                    $checker.text(__('Uncheck all'));
                }

                return false;
            });
        },

        /**
         * init special forms controls
         */
        initOntoForms: function () {


            //open the authoring tool on the authoringOpener button
            $('.authoringOpener').click(function () {
                var tabUrl = getUrl('authoring'),
                    tabId = 'panel-' + module.config().module.toLowerCase() + '_authoring',
                    $tabContainer = $('#tabs'),
                    $panel = (function() {
                        var $wantedPanel = $tabContainer.find('#' + tabId);

                        if(!$wantedPanel.length) {
                            $wantedPanel = $('<div>', { id: tabId, 'class': 'clear content-panel' }).hide();
                            $tabContainer.find('.content-panel').after($wantedPanel);
                        }
                        return $wantedPanel;
                    }());

                $.ajax({
                    type: "GET",
                    url: tabUrl,
                    data: {
                        uri: $("#uri").val(),
                        classUri: $("#classUri").val()
                    },
                    dataType: 'html',
                    success: function (responseHtml) {
                        $tabContainer.find('.content-panel').not($panel).hide();
                        window.location.hash = tabId;
                        responseHtml = $(responseHtml);
                        responseHtml.find('#authoringBack').click(function () {
                            var $myPanel = $(this).parents('.content-panel'),
                                $otherPanel = $myPanel.prev();
                            $myPanel.hide();
                            $otherPanel.show();
                        });
                        $panel.html(responseHtml).show();
                    }
                });
            });

            $('input.editVersionedFile').each(function () {
                var infoUrl = context.root_url + 'tao/File/getPropertyFileInfo';
                var data = {
                    'uri': $("#uri").val(),
                    'propertyUri': $(this).siblings('label.form_desc').prop('for')
                };
                var $_this = $(this);
                $.ajax({
                    type: "GET",
                    url: infoUrl,
                    data: data,
                    dataType: 'json',
                    success: function (r) {
                        $_this.after('<span>' + r.name + '</span>');
                    }
                });
            }).click(function () {
                var data = {
                    'uri': $("#uri").val(),
                    'propertyUri': $(this).siblings('label.form_desc').prop('for')
                };

                helpers.getMainContainer().load(getUrl('editVersionedFile'), data);
                return false;
            });

            function buildClassPropertiesAuthoringURL(action) {
                const shownExtensions = context.shownExtension;

                let extension = 'tao';
                let controller = 'PropertiesAuthoring';

                if (shownExtensions === 'taoItems') {
                    extension = shownExtensions;
                    controller = 'Items';
                }

                return helpers._url(action, controller, extension);
            }

            /**
             * Validate if property has a dependency
             */
            async function checkForDependency(propertyUri) {
                try {
                    const url = urlUtil.route('getDependentProperties', 'PropertyValues', 'tao', { propertyUri })
                    const response = await request({ url, method: 'GET', dataType: 'json'})
                    if (response.success && response.data) { return response.data; }
                    else { throw response; }
                } catch (err) {
                    console.error(err);
                    return null;
                }
            }

            function regularConfirmantion() {
                return window.confirm(__('Please confirm property deletion!')); 
            }

            async function getPropertyRemovalConfirmation($groupNode, uri) {
                const dependencies = await checkForDependency(uri);

                return new Promise((resolve, reject) => {
                    if (!dependencies.length) {
                        return regularConfirmantion() ? resolve() : reject();
                    } else {
                        const name = $groupNode.find('.property-heading-label')[0].innerText;
                        const dependantPropName = dependencies.reduce((prev, next, index) => {
                            const delimiter = index === dependencies.length - 1 ? '' : ', '
                            return prev + `${next.label}${delimiter}`;
                        }, '');

                        confirmDialog(
                            `<b>${name}</b>
                            ${__('currently has a dependency established with ')}
                            <b>${dependantPropName}</b>.
                            ${__('Deleting this property will also remove the dependency')}. 
                            <br><br> ${__('Are you wish to delete it')}?`,
                            resolve,
                            reject
                        );
                    }
                })
            }

            /**
             * remove a form group, ie. a property
             */
            async function removePropertyGroup() {
                const $groupNode = $(this).closest(".form-group");
                try {
                    await getPropertyRemovalConfirmation($groupNode, $(this).data("uri"));
                } catch (err) { return; }       
                
                property.remove(
                    $(this).data("uri"),
                    $("#id").val(),
                    buildClassPropertiesAuthoringURL('removeClassProperty'),
                    function() {
                        $groupNode.remove();
                    }
                );

                document.getElementById('item-class-schema').click();
            }

            //property delete button
            $(".property-deleter").off('click').on('click', removePropertyGroup);

            //property add button
            $(".property-adder").off('click').on('click', function (e) {
                e.preventDefault();

                property.add($("#id").val(), buildClassPropertiesAuthoringURL('addClassProperty'));
            });

            $(".index-adder").off('click').on('click', function (e) {
                e.preventDefault();
                var $prependTo = $(this).closest('div');
                var $groupNode = $(this).closest(".form-group");
                if ($groupNode.length) {
                    var max = 0;
                    var $propertyindex = $('.property-uri', $groupNode);
                    var propertyindex = parseInt($propertyindex.attr('id').replace(/[\D]+/, ''));


                    $groupNode.find('[data-index]').each(function(){
                        if(max < $(this).data('index')){
                            max = $(this).data('index');
                        }
                    });

                    max = max + 1;
                    var uri = $groupNode.find('.property-uri').val();
                    $.ajax({
                        type: "GET",
                        url: helpers._url('addPropertyIndex', 'PropertiesAuthoring', 'tao'),
                        data: {uri : uri, index : max, propertyIndex : propertyindex},
                        dataType: 'json',
                        success: function (response) {
                            $prependTo.before(response.form);
                        }
                    });
                }
            });

            $('.property-edit-container').off('click', '.index-remover').on('click', '.index-remover', function(e){
                e.preventDefault();
                var $groupNode = $(this).closest(".form-group");
                var uri = $groupNode.find('.property-uri').val();

                var $editContainer = $($groupNode[0]).children('.property-edit-container');
                $.ajax({
                    type: "POST",
                    url: helpers._url('removePropertyIndex', 'PropertiesAuthoring', 'tao'),
                    data: {uri : uri, indexProperty : $(this).attr('id')},
                    dataType: 'json',
                    success: function (response) {
                        var $toRemove = $('[id*="'+response.id+'"], [data-related-index="'+response.id+'"]');
                        $toRemove.each(function(){
                            var $currentTarget = $(this);
                            while(!_.isEqual($currentTarget.parent()[0], $editContainer[0]) && $currentTarget.parent()[0] !== undefined){
                                $currentTarget = $currentTarget.parent();
                            }
                            $currentTarget.remove();
                        });
                    }
                });
            });

            $(".property-mode").off('click').on('click', function () {
                var $btn = $(this);
                var mode = 'simple';
                var url;

                if ($btn.hasClass('disabled')) {
                    return;
                }

                if ($btn.hasClass('property-mode-advanced')) {
                    mode = 'advanced';
                }
                url = $btn.parents('form').prop('action');

                helpers.getMainContainer().load(url, {
                    'property_mode': mode,
                    'uri': $("#uri").val(),
                    'id': $("#id").val(),
                    'classUri': $("#classUri").val()
                });

                return false;
            });

            /**
             * display or not the list regarding the property type
             */
            function showPropertyList(e, isInit) {
                var $this = $(this);
                var $elt = $this.parent("div").next("div");
                var propertiesTypes = ['list','tree'];

                var re = new RegExp(propertiesTypes.join('$|').concat('$'));
                if (re.test($this.val())) {
                    if ($elt.css('display') === 'none') {
                        $elt.show();
                        $elt.find('select').removeAttr('disabled');
                    }
                }
                else if ($elt.css('display') !== 'none') {
                    $elt.css('display', 'none');
                    $elt.find('select').prop('disabled', false);
                    $elt.find('select option[value=" "]').attr('selected', 'selected').trigger('change');
                }

                $.each(propertiesTypes, function (i, rangedPropertyName) {
                    var re = new RegExp(rangedPropertyName + '$');
                    if (re.test($this.val())) {
                        const $propValuesSelect = $elt.find('select');
                        const propValue = $propValuesSelect.val();
                        $propValuesSelect.html($elt.closest('.property-edit-container').find('.' + rangedPropertyName + '-template').html());
                        const $selectedInTemplate = $propValuesSelect.find('option[selected]');

                        if (!propValue || !propValue.trim()) {
                            if (!isInit && $selectedInTemplate.length) {
                                $propValuesSelect.find('option[value=" "]').attr('selected', 'selected');
                            }

                            return true;
                        }

                        if ($(`option[value="${propValue}"]`, $propValuesSelect).length) {
                            $propValuesSelect.val(propValue);
                        }

                        return true;
                    }
                });
            }


            function clearPropertyListValues() {
                $(this).parent("div").parent("div").children("ul.form-elt-list").remove();
            }

            /**
             * by selecting a list, the values are displayed
             */
            function showPropertyListValues() {
                const $this = $(this);
                const elt = $this.parent("div");
                let classUri;

                //load the instances and display them (the list items)
                $(elt).parent("div").children("ul.form-elt-list").remove();
                classUri = $this.val();
                if (classUri && classUri.trim()) {
                    $this.parent("div").children("div.form-error").remove();
                    $.ajax({
                        url: context.root_url + 'taoBackOffice/Lists/getListElements',
                        type: "POST",
                        data: {listUri: classUri},
                        dataType: 'json',
                        success: function (response) {
                            let html = "<ul class='form-elt-list'>",
                                property;
                            for (property in response) {
                                if(!response.hasOwnProperty(property)) {
                                    continue;
                                }
                                html += '<li>' + encode.html(response[property]) + '</li>';
                            }
                            html += '</ul>';
                            $(elt).after(html);
                        }
                    });
                }
            }

            function showDependsOnProperty() {
            	const $this = $(this);
                const classUri = $(document.getElementById('classUri')).val();
                let propertyUriToSend;
            	const listUri = $this.val();
                const dependsId = $(this)[0].id.match(/\d+_/)[0];
                const dependsOnSelect = $(document.getElementById(`${dependsId}depends-on-property`));
                propertyUriToSend = $this.parent().parent().parent()[0].id;
                propertyUriToSend = propertyUriToSend.replace('property_', '');
                $.ajax({
                    url: context.root_url + 'tao/PropertyValues/getDependOnPropertyList',
                    type: "GET",
                    data: {
                        class_uri: classUri,
                        list_uri: listUri,
                        property_uri: propertyUriToSend,
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response && response.data && response.data.length !== 0) {
                            const backendValues = response.data.reduce(
                                (accumulator, currentValue) => {
                                    accumulator.push(currentValue.uriEncoded);
                                    return accumulator;
                                },
                                []
                            );
                            const currentValues = Object
                                .values(dependsOnSelect[0].options)
                                .map(entry => entry.value)
                                .filter(entry => entry !== ' ');
                            let haveSameData = false;
                            currentValues.map(entry => {
                                if (!backendValues.includes(entry)) {
                                    haveSameData = true;
                                }
                                return;
                            });
                            if (dependsOnSelect[0].length <= 1 || haveSameData) {
                                let html = '<option value=" "> --- select --- </option>';
                                for (const propertyData in response.data) {
                                    html += `<option value="${response.data[propertyData].uri}">${response.data[propertyData].label}</option>`;
                                }
                                dependsOnSelect.empty().append(html);
                            }
                            dependsOn.toggle();
                        } else {
                            dependsOnSelect.parent().hide();
                        }
                    }
                });
            }

            function onTypeChange(e, flag) {
                showPropertyList.bind(this)(e, flag === 'initial');
            }

            function onListValuesChange(e) {
                clearPropertyListValues.bind(this)(e);
                if (!$(this).val() || !$(this).val().trim()) {
                    $(this).find('option[value=" "]').attr('selected', 'selected');
                }
                showPropertyListValues.bind(this)(e);
                showDependsOnProperty.bind(this)(e);
            }

            //bind functions to the drop down:

            $('.property-template').each(function(){
                $(this).closest('div').hide();
            });

            //display the values drop down regarding the selected type
            var $propertyType = $(".property-type"),
                $propertyListValues = $(".property-listvalues");

            $propertyType.on('change', onTypeChange).trigger('change', 'initial');

            //display the values of the selected list
            $propertyListValues.off('change');
            $propertyListValues.on('change', onListValuesChange).trigger('change');

            $propertyListValues.each(function () {
                var elt = $(this).parent("div");
                if (!elt.hasClass('form-elt-highlight') && elt.css('display') !== 'none') {
                    elt.addClass('form-elt-highlight');
                }
            });
        },

        /**
         * controls of the translation forms
         */
        initTranslationForm: function initTranslationForm () {
            var self = this;
            $('#translate_lang').change(function () {
                var trLang = $(this).val();
                if (trLang !== '') {
                    $("#translation_form").find(":input").each(function () {
                        if (/^http/.test($(this).prop('name'))) {
                            $(this).val('');
                        }
                    });
                    $.post(
                        getUrl('getTranslatedData'),
                        {uri: $("#uri").val(), classUri: $("#classUri").val(), lang: trLang},
                        function (response) {
                            var index;
                            var formElt;
                            for (index in response) {
                                formElt = $(":input[name='" + index + "']");
                                if (formElt.hasClass('html-area') && self.htmlEditors[index]) {
                                    self.htmlEditors[index].setData(response[index]);
                                }
                                else {
                                    formElt.val(response[index]);
                                }
                            }
                        },
                        'json'
                    );
                }
            });
        },

        /**
         * Ajax form submit -> post the form data and display back the form into the container
         * @param myForm
         * @param serialize
         * @return boolean
         */
        submitForm: function submitForm(myForm, serialize) {
            var self = this;
            var $container;

            try {
                if (myForm.prop('enctype') === 'multipart/form-data' && myForm.find(".file-uploader").length) {
                    return false;
                }
                else {
                    //FIXME should use sectionAPI instead
                    $container = myForm.closest('.content-block');
                    if (!$container || $container.length === 0) {
                        return true;//go to the link
                    }
                    else {
                        //if a ckeditor is in the form we need to sync the textarea content
                        $('.html-area', myForm).each(function(){
                            if(self.htmlEditors[this.id]){
                                self.htmlEditors[this.id].updateElement();
                            }
                        });

                        serialize = typeof serialize !== 'undefined' ? serialize : myForm.serializeArray();

                        $('.current-submitter', myForm).each(function () {
                            $(this).removeClass('current-submitter');
                            if (Array.isArray(serialize)) {
                                serialize.push({name: this.name, value: this.value});
                            } else {
                                serialize[this.name] = this.value;
                            }
                        });

                        $('[data-depends-on-property][disabled]', myForm).each(function () {
                            if (Array.isArray(serialize)) {
                                serialize.push({name: this.name, value: this.value});
                            } else {
                                serialize[this.name] = this.value;
                            }
                        });

                        $container.load(myForm.prop('action'), serialize);
                    }
                }
            }
            catch (exp) {
                return false;
            }
            return false;
        }
    };

    return UiForm;
});
