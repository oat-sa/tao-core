define(['jquery', 'i18n', 'helpers', 'context'], function ($, __, helpers, context) {

    function _addSquareBtn(title, icon, $listToolBar) {
        var $btn = $('<button>', { 'class': 'btn-info small lft square ' + icon + '-btn', title: __(title) });
        $btn.prepend($('<span>', { 'class': 'icon-' + icon }));
        $listToolBar.append($btn);
        return $btn;
    }

    return {

        start: function () {

            var saveUrl = helpers._url('saveLists', 'Lists', 'tao');
            var delListUrl = helpers._url('removeList', 'Lists', 'tao');
            var delEltUrl = helpers._url('removeListElement', 'Lists', 'tao');

            $(".list-edit-btn").click(function () {
                var $btn = $(this),
                    uri = $btn.data('uri'),
                    $listContainer = $("div[id='list-data_" + uri + "']"),
                    // form must be on the inside rather than on the outside as it has been in 2.6
                    $listForm     = $listContainer.find('form'),
                    $listTitleBar = $listContainer.find('.container-title'),
                    $listToolBar  = $listContainer.find('.data-container-footer'),
                    $listSaveBtn,
                    $listNewBtn;

                if (!$listForm.length) {

                    $listForm = $('<form>');
                    $listContainer.wrapInner($listForm);
                    $listForm.append("<input type='hidden' name='uri' value='" + uri + "' />");

                    $("<input type='text' name='label' value='" + $listTitleBar.text() + "'/>")
                        .prependTo($listContainer.find('div.list-elements'))
                        .keyup(function () {
                            $listTitleBar.text($(this).val());
                        });

                    if ($listContainer.find('.list-element').length) {
                        $listContainer.find('.list-element').replaceWith(function () {
                            return "<input type='text' name='" + $(this).attr('id') + "' value='" + $(this).text() + "' />";
                        });
                    }

                    var elementList = $listContainer.find('ol');
                    elementList.addClass('sortable-list');
                    elementList.find('li').addClass('ui-state-default');
                    elementList.find('li').prepend('<span class="ui-icon ui-icon-grip-dotted-vertical" ></span>');
                    elementList.find('li').prepend('<span class="ui-icon ui-icon-arrowthick-2-n-s" ></span>');
                    elementList.find('li').append('<span class="ui-icon ui-icon-circle-close list-element-deletor" style="cursor:pointer;" ></span>');

                    elementList.sortable({
                        axis: 'y',
                        opacity: 0.6,
                        placeholder: 'ui-state-error',
                        tolerance: 'pointer',
                        update: function (event, ui) {
                            var map = {};
                            $.each($(this).sortable('toArray'), function (index, id) {
                                map[id] = 'list-element_' + (index + 1);
                            });
                            $(this).find('li').each(function () {
                                var id = $(this).attr('id');
                                if (map[id]) {
                                    $(this).attr('id', map[id]);
                                    var newName = $(this).find('input').attr('name').replace(id, map[id]);
                                    $(this).find('input').attr('name', newName);
                                }
                            });
                        }
                    });

                    $listSaveBtn = _addSquareBtn('Save element', 'save', $listToolBar);
                    $listSaveBtn.on('click', function () {
                        $.postJson(
                            saveUrl,
                            $(this).parents('form').serializeArray(),
                            function (response) {
                                if (response.saved) {
                                    helpers.createInfoMessage(__("list saved"));
                                    helpers._load(helpers.getMainContainerSelector(), helpers._url('index', 'Lists', 'tao'));
                                }
                            }
                        );
                    });

                    $listNewBtn = _addSquareBtn('New element', 'add', $listToolBar);
                    $listNewBtn.click(function () {
                        var level = $(this).parent().find('ol').children().length + 1;
                        $(this).parent().find('ol').append(
                            "<li id='list-element_" + level + "' class='ui-state-default'>" +
                                "<span class='ui-icon ui-icon-arrowthick-2-n-s' ></span>" +
                                "<span class='ui-icon ui-icon-grip-dotted-vertical' ></span>" +
                                "<input type='text' name='list-element_" + level + "_' />" +
                                "<span class='icon-add list-element-delete-btn' ></span>" +
                                "</li>");
                    });
                }

                $(".list-element-delete-btn").click(function () {
                    var $btn = $(this),
                        uri = $btn.data('uri');
                    if (confirm(__("Please confirm you want to delete this list element."))) {
                        var element = $(this).parent();
                        uri = element.find('input:text').attr('name').replace(/^list\-element\_([1-9]*)\_/, '');
                        $.postJson(
                            delEltUrl,
                            {uri: uri},
                            function (response) {
                                if (response.deleted) {
                                    element.remove();
                                    helpers.createInfoMessage(__("Element deleted"));
                                }
                            }
                        );
                    }
                });
            });

            $('.list-delete-btn').click(function () {
                if (confirm(__("Please confirm you want to delete this list. This operation cannot be undone."))) {
                    var $btn = $(this),
                        uri = $btn.data('uri'),
                        $list = $(this).parents(".data-container");
                    $.postJson(
                        delListUrl,
                        {uri: uri},
                        function (response) {
                            if (response.deleted) {
                                helpers.createInfoMessage(__("List deleted"));
                                $list.remove();
                            }
                        }
                    );
                }
            });
        }
    };
});


