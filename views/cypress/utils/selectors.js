export default {
    deleteTest: '[data-context="resource"][data-action="removeNode"]',
    deleteClass: '[data-context="resource"][data-action="removeNode"]',
    addTest: '[data-context="resource"][data-action="instanciate"]',
    addUserForm: 'form[action="/tao/Users/add"]',
    addTestTakerForm: 'form[action="/taoTestTaker/TestTaker/editSubject"]',
    labelSelector: '[data-testid=Label]',
    // TODO: Replace update selector when data-testid attributes will be aded
    manageUserTable: '#user-list',
    testClassForm: 'form[action="/taoTests/Tests/editClassLabel"]',
    deleteConfirm: '[data-control="ok"]',
    root: '[data-uri="http://www.tao.lu/Ontologies/TAOTest.rdf#Test"]'
};
