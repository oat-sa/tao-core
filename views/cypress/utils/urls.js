export default {
    index: '/tao/Main/index',
    login: '/tao/Main/login',
    addUser: '/tao/Main/index?structure=users&ext=tao&section=add_user',
    itemsManager: '/tao/Main/index?structure=items&ext=taoItems&section=manage_items',
    manageUsers: '/tao/Main/index?structure=users&ext=tao&section=list_users',
    mediaManager: '/tao/Main/index?structure=taoMediaManager&ext=taoMediaManager&section=media_manager',
    testsManager: '/tao/Main/index?structure=tests&ext=taoTests&section=manage_tests',
    testTakersManager: '/tao/Main/index?structure=TestTaker&ext=taoTestTaker&section=manage_test_takers',
    settings: {
        index: '/tao/Main/index?structure=settings',
        list: '/tao/Main/index?structure=settings&ext=tao&section=taoBo_list',
        tree: '/tao/Main/index?structure=settings&ext=tao&section=taoBo_tree'
    },
    list: {
        index: '**/taoBackOffice/Lists/index',
        save: '**/taoBackOffice/Lists/saveLists',
        remove: '**/taoBackOffice/Lists/removeList'
    },
    edit: '**/edit*',
};
