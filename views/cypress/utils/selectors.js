export default {
    addUserForm: 'form[action="/tao/Users/add"]',

    deleteConfirm: '[data-control="ok"]',

    labelSelector: '[data-testid=Label]',

    manageUserTable: '#user-list',

    // Search
    search: {
        textInput: 'input[name="query"]',
        openResultsButton: 'button[title="Open results"]',

        modal: {
            // TODO: add data-attributes to remove .class selectors
            dialog: '.search-modal',
            textInput: '.search-modal input[placeholder="Search Item"]',
            entries: '.search-modal [data-item-identifier]',
            filterButton: '.search-modal .class-filter',
            closeButton: '#modal-close-btn',
            paginationButton: '.search-modal .pagination button',
        }
    }
};
