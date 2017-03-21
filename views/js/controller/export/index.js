/**
 * @author Jérôme Bogaert <jerome@taotesting.com>
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define(['jquery', 'i18n', 'helpers', 'layout/section', 'ui/feedback', 'ui/taskQueue/table'], function($, __, helpers, section, feedback, taskQueueTableFactory) {
    'use strict';


    /**
     * The user index controller
     * @exports controller/users/index
     */
    return {
        start : function(){
            var $queueArea = $('#task-list');


            taskQueueTableFactory({
                rows : 10,
                replace : true,
                context : 'Export',
                dataUrl : helpers._url('getTasks', 'TaskQueueData', 'tao'),
                statusUrl : helpers._url('getStatus', 'TaskQueueData', 'tao'),
                removeUrl : helpers._url('archiveTask', 'TaskQueueData', 'tao'),
                downloadUrl : helpers._url('outputFile', 'Export', 'tao')
            })
                .init()
                .render($queueArea);
        }
    };
});
