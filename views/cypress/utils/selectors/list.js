export default {
    lists: '[id^="list-data"]',
    listLast: '[id^="list-data"]:last-child',

    maxItems: '[data-testid="maxItems"]',

    // List
    listName: '[data-testid="listName"]',
    createListButton: '#createList button',//-

    listEditButton: '[data-testid="listEditButton"]',
    listDeleteButton: '[data-testid="listDeleteButton"]',

    listNameInput: '[data-testid="listNameInput"]',
    uriElementsInput: ['id^="uri_list-element'],
    editUriCheckbox: '[data-testid="editUriCheckbox"]',

    // Element
    elementsList: '[data-testid="elements"]',
    elementNameInput: '[data-testid="elementNameInput"]',
    elementUriInput: '[data-testid="elementUriInput"]',

    addElementButton: '[data-testid="addElementButton"]',
    saveElementButton: '[data-testid="saveElementButton"]',
    deleteElementButton: '[data-testid="deleteElementButton"]',
};
